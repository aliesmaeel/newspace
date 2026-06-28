<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\EventPromoCode;
use App\Models\EventRegistration;
use App\Mail\AdminBookingCreatedMail;
use App\Models\Event;
use App\Services\EventRegistrationNotificationService;
use App\Services\IntegrationSettingsService;
use App\Services\StripeCheckoutService;
use App\Services\StripeTransactionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    private function resolveAppointmentForSession(object $session): ?Appointment
    {
        $sessionId = (string) ($session->id ?? '');
        if ($sessionId !== '') {
            $appointment = Appointment::query()
                ->where('stripe_checkout_session_id', $sessionId)
                ->first();

            if ($appointment) {
                return $appointment;
            }
        }

        $metadataAppointmentId = (int) ($session->metadata->appointment_id ?? 0);
        if ($metadataAppointmentId > 0) {
            return Appointment::query()->find($metadataAppointmentId);
        }

        return null;
    }

    public function __invoke(
        Request $request,
        StripeCheckoutService $stripe,
        IntegrationSettingsService $settings,
        StripeTransactionRecorder $transactions,
        EventRegistrationNotificationService $eventRegistrationNotifications,
    )
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');
        $secret = $stripe->webhookSecret();

        if ($secret === '') {
            return response()->json(['message' => 'Stripe webhook secret is missing.'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid webhook signature.'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $appointment = $this->resolveAppointmentForSession($session);

            if ($appointment) {
                $appointment->update([
                    'status' => 'approved',
                    'payment_status' => 'paid',
                    'stripe_checkout_session_id' => (string) ($session->id ?? $appointment->stripe_checkout_session_id),
                    'stripe_payment_intent_id' => (string) ($session->payment_intent ?? ''),
                    'paid_at' => now(),
                    'approved_at' => now(),
                ]);

                $adminEmail = $settings->mailAdminAddress();
                if ($adminEmail) {
                    try {
                        $dashboardUrl = rtrim(config('app.url'), '/') . "/admin/appointments/{$appointment->id}/edit";
                        Mail::to($adminEmail)->send(
                            new AdminBookingCreatedMail($appointment, $dashboardUrl, 'New paid appointment booking confirmed')
                        );
                    } catch (Throwable $e) {
                        report($e);
                    }
                }

                $transactions->recordCheckoutSession($session, $appointment->email, 'paid');

            }

            $eventRegistrationId = (int) ($session->metadata->event_registration_id ?? 0);
            if ($eventRegistrationId > 0) {
                $eventRegistration = EventRegistration::query()->find($eventRegistrationId);
                if ($eventRegistration) {
                    $wasConfirmed = $eventRegistration->status === 'confirmed';

                    $eventRegistration->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'stripe_checkout_session_id' => (string) ($session->id ?? $eventRegistration->stripe_checkout_session_id),
                        'registered_at' => now(),
                    ]);

                    if (! $wasConfirmed && $eventRegistration->event_promo_code_id) {
                        EventPromoCode::query()->whereKey($eventRegistration->event_promo_code_id)->increment('uses_count');
                    }

                    $event = Event::query()->find($eventRegistration->event_id);
                    if ($event) {
                        $transactions->recordEventRegistrationCheckout($session, $eventRegistration, $event, 'paid');
                    }

                    $eventRegistrationNotifications->sendConfirmation($eventRegistration);
                }
            }
        }

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;
            $appointment = $this->resolveAppointmentForSession($session);

            if ($appointment) {
                if ($appointment->status === 'pending_payment') {
                    $appointment->update([
                        'payment_status' => 'failed',
                        'status' => 'rejected',
                        'stripe_checkout_session_id' => (string) ($session->id ?? $appointment->stripe_checkout_session_id),
                    ]);
                }

                $transactions->recordCheckoutSession($session, $appointment->email, 'failed');
            }

            $eventRegistrationId = (int) ($session->metadata->event_registration_id ?? 0);
            if ($eventRegistrationId > 0) {
                $eventRegistration = EventRegistration::query()->with('user')->find($eventRegistrationId);
                $event = $eventRegistration ? Event::query()->find($eventRegistration->event_id) : null;
                if ($eventRegistration && $event) {
                    $transactions->recordEventRegistrationCheckout($session, $eventRegistration, $event, 'failed');
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
