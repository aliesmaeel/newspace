<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPromoCode;
use App\Models\EventRegistration;
use App\Models\Transaction;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Stripe;

class StripeEventCheckoutService
{
    public function __construct(
        private IntegrationSettingsService $settings,
        private StripeTransactionRecorder $transactions,
        private EventRegistrationNotificationService $notifications,
    ) {}

    public function createSession(EventRegistration $registration, Event $event, ?string $returnBaseUrl = null, ?EventPromoCode $promo = null): Session
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

        $sessionPayload = [
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
        ];

        $couponId = $this->resolveCouponId($promo);
        if ($couponId !== null) {
            $sessionPayload['discounts'] = [['coupon' => $couponId]];
        }

        $session = Session::create($sessionPayload);

        $this->transactions->recordEventRegistrationCheckout(
            $session,
            $registration,
            $event,
            'pending',
        );

        return $session;
    }

    /**
     * Ensure a Stripe Coupon exists for a partial-discount promo code and return its id.
     * Returns null when there is no applicable discount (no promo, full/zero discount).
     */
    private function resolveCouponId(?EventPromoCode $promo): ?string
    {
        if ($promo === null) {
            return null;
        }

        $percentage = (int) $promo->discount_percentage;
        if ($percentage <= 0 || $percentage >= 100) {
            return null;
        }

        $existing = trim((string) $promo->stripe_coupon_id);
        if ($existing !== '') {
            return $existing;
        }

        $coupon = Coupon::create([
            'percent_off' => $percentage,
            'duration' => 'once',
            'name' => $promo->code,
            'metadata' => [
                'event_promo_code_id' => (string) $promo->id,
                'event_id' => (string) $promo->event_id,
            ],
        ]);

        $promo->update(['stripe_coupon_id' => $coupon->id]);

        return $coupon->id;
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

        $wasConfirmed = $registration->status === 'confirmed';

        $registration->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'stripe_checkout_session_id' => (string) ($session->id ?? $sessionId),
            'registered_at' => $registration->registered_at ?? now(),
        ]);

        if (! $wasConfirmed && $registration->event_promo_code_id) {
            EventPromoCode::query()->whereKey($registration->event_promo_code_id)->increment('uses_count');
        }

        $event = Event::query()->find($registration->event_id);
        if ($event) {
            $this->transactions->recordEventRegistrationCheckout($session, $registration, $event, 'paid');
        }

        $this->notifications->sendConfirmation($registration);

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
