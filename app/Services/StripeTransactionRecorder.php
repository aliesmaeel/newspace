<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;

class StripeTransactionRecorder
{
    /**
     * @param  array<string, mixed>  $payloadExtras
     */
    public function recordCheckoutSession(
        object $session,
        string $customerEmail,
        string $status,
        string $type = 'checkout',
        array $payloadExtras = [],
    ): Transaction {
        $sessionId = (string) ($session->id ?? '');
        $payload = is_array($session)
            ? $session
            : json_decode(json_encode($session), true);

        if (! is_array($payload)) {
            $payload = [];
        }

        if ($payloadExtras !== []) {
            $payload = array_merge($payload, $payloadExtras);
        }

        $attributes = [
            'appointment_email' => $customerEmail,
            'gateway' => 'stripe',
            'type' => $type,
            'status' => $status,
            'amount_cents' => (int) ($session->amount_total ?? 0),
            'currency' => (string) ($session->currency ?? config('services.stripe.currency', 'gbp')),
            'payment_intent_id' => (string) ($session->payment_intent ?? ''),
            'payload' => $payload,
        ];

        if ($status === 'paid') {
            $attributes['paid_at'] = now();
        }

        return Transaction::query()->updateOrCreate(
            ['external_id' => $sessionId],
            $attributes,
        );
    }

    public function recordEventRegistrationCheckout(
        object $session,
        EventRegistration $registration,
        Event $event,
        string $status,
    ): Transaction {
        $registration->loadMissing('user');

        return $this->recordCheckoutSession(
            $session,
            (string) $registration->user->email,
            $status,
            'event_checkout',
            [
                'event_registration_id' => $registration->id,
                'event_id' => $event->id,
                'event_slug' => $event->slug,
                'event_title' => $event->title,
            ],
        );
    }
}
