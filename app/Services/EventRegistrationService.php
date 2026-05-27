<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPromoCode;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class EventRegistrationService
{
    public function __construct(
        private StripeEventCheckoutService $checkout,
    ) {}

    /**
     * @return array{status: string, message?: string, checkout_url?: string, registration_id?: int}
     */
    public function register(User $user, Event $event, ?string $promoCodeInput = null, ?string $returnBaseUrl = null): array
    {
        $existing = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->status === 'confirmed') {
            throw new RuntimeException('You are already registered for this event.');
        }

        $promo = $this->resolvePromoCode($event, $promoCodeInput);
        $isFirstEventFree = ! EventRegistration::query()
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->exists();

        $isFree = $isFirstEventFree
            || $promo !== null
            || (int) $event->price_cents < 100;

        if ($existing) {
            $registration = $existing;
        } else {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);
        }

        if ($isFree) {
            $registration->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'used_first_event_free' => $isFirstEventFree,
                'event_promo_code_id' => $promo?->id,
                'registered_at' => now(),
            ]);

            if ($promo) {
                $promo->increment('uses_count');
            }

            return [
                'status' => 'confirmed',
                'message' => $isFirstEventFree
                    ? 'Your first event is free. You are registered!'
                    : ($promo ? 'Promo code applied. You are registered!' : 'You are registered!'),
                'registration_id' => $registration->id,
            ];
        }

        if (trim((string) $event->stripe_price_id) === '') {
            throw new RuntimeException('This event is not synced to Stripe yet. Please contact support.');
        }

        $session = $this->checkout->createSession($registration, $event, $returnBaseUrl);

        $registration->update([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'stripe_checkout_session_id' => $session->id,
            'event_promo_code_id' => null,
            'used_first_event_free' => false,
        ]);

        return [
            'status' => 'checkout',
            'checkout_url' => $session->url,
            'registration_id' => $registration->id,
        ];
    }

    private function resolvePromoCode(Event $event, ?string $input): ?EventPromoCode
    {
        $code = Str::upper(trim((string) $input));
        if ($code === '') {
            return null;
        }

        $promo = EventPromoCode::query()
            ->where('event_id', $event->id)
            ->where('code', $code)
            ->first();

        if (! $promo || ! $promo->isValid()) {
            throw new RuntimeException('This promo code is invalid or expired.');
        }

        return $promo;
    }
}
