<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Transaction;
use App\Mail\AdminBookingCreatedMail;
use App\Services\IntegrationSettingsService;
use App\Services\StripeCheckoutService;
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

    public function __invoke(Request $request, StripeCheckoutService $stripe, IntegrationSettingsService $settings)
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

                Transaction::query()->updateOrCreate(
                    [
                        'external_id' => (string) $session->id,
                    ],
                    [
                        'appointment_email' => $appointment->email,
                        'gateway' => 'stripe',
                        'type' => 'checkout',
                        'status' => 'paid',
                        'amount_cents' => (int) ($session->amount_total ?? 0),
                        'currency' => (string) ($session->currency ?? 'usd'),
                        'payment_intent_id' => (string) ($session->payment_intent ?? ''),
                        'payload' => is_array($session) ? $session : json_decode(json_encode($session), true),
                        'paid_at' => now(),
                    ]
                );

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

                Transaction::query()->updateOrCreate(
                    [
                        'external_id' => (string) $session->id,
                    ],
                    [
                        'appointment_email' => $appointment->email,
                        'gateway' => 'stripe',
                        'type' => 'checkout',
                        'status' => 'failed',
                        'amount_cents' => (int) ($session->amount_total ?? 0),
                        'currency' => (string) ($session->currency ?? 'usd'),
                        'payment_intent_id' => (string) ($session->payment_intent ?? ''),
                        'payload' => is_array($session) ? $session : json_decode(json_encode($session), true),
                    ]
                );
            }
        }

        return response()->json(['received' => true]);
    }
}
