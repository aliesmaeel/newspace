<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeEventCheckoutService
{
    public function __construct(
        private IntegrationSettingsService $settings,
        private StripeTransactionRecorder $transactions,
    ) {}

    public function createSession(EventRegistration $registration, Event $event, ?string $returnBaseUrl = null): Session
    {
        $priceId = trim((string) $event->stripe_price_id);
        if ($priceId === '') {
            throw new RuntimeException('Stripe price is missing for this event.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing.');
        }

        Stripe::setApiKey($secretKey);

        $appUrl = rtrim((string) ($returnBaseUrl ?: config('app.url')), '/');

        $registration->loadMissing('user');

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => "{$appUrl}/events/{$event->slug}?registration=success&session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => "{$appUrl}/events/{$event->slug}?registration=cancelled",
            'customer_email' => $registration->user->email,
            'line_items' => [[
                'quantity' => 1,
                'price' => $priceId,
            ]],
            'metadata' => [
                'event_registration_id' => (string) $registration->id,
                'event_id' => (string) $event->id,
                'user_id' => (string) $registration->user_id,
            ],
        ]);

        $this->transactions->recordEventRegistrationCheckout(
            $session,
            $registration,
            $event,
            'pending',
        );

        return $session;
    }

    /**
     * Confirm registration when Stripe checkout is paid (webhook fallback for local/dev or delayed webhooks).
     */
    public function syncRegistrationPayment(EventRegistration $registration): bool
    {
        if ($registration->status === 'confirmed' && $registration->payment_status === 'paid') {
            $this->recordTransactionIfMissing($registration);

            return true;
        }

        $sessionId = trim((string) $registration->stripe_checkout_session_id);
        if ($sessionId === '') {
            return false;
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            return false;
        }

        Stripe::setApiKey($secretKey);

        $session = Session::retrieve($sessionId);
        $registrationId = (int) ($session->metadata->event_registration_id ?? 0);

        if ($registrationId !== (int) $registration->id) {
            return false;
        }

        if (($session->payment_status ?? '') !== 'paid') {
            return false;
        }

        $registration->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'stripe_checkout_session_id' => (string) ($session->id ?? $sessionId),
            'registered_at' => $registration->registered_at ?? now(),
        ]);

        $event = Event::query()->find($registration->event_id);
        if ($event) {
            $this->transactions->recordEventRegistrationCheckout($session, $registration, $event, 'paid');
        }

        return true;
    }

    private function recordTransactionIfMissing(EventRegistration $registration): void
    {
        $sessionId = trim((string) $registration->stripe_checkout_session_id);
        if ($sessionId === '' || Transaction::query()->where('external_id', $sessionId)->exists()) {
            return;
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            return;
        }

        Stripe::setApiKey($secretKey);

        try {
            $session = Session::retrieve($sessionId);
        } catch (\Throwable) {
            return;
        }

        if (($session->payment_status ?? '') !== 'paid') {
            return;
        }

        $event = Event::query()->find($registration->event_id);
        if ($event) {
            $this->transactions->recordEventRegistrationCheckout($session, $registration, $event, 'paid');
        }
    }
}
