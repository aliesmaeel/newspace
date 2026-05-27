<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Program;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService
{
    private const MIN_AMOUNT_CENTS = 100;

    public function __construct(private IntegrationSettingsService $settings) {}

    public function getProgramBySlug(string $slug): ?Program
    {
        return Program::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function createSession(Appointment $appointment, ?string $returnBaseUrl = null): Session
    {
        $program = $this->getProgramBySlug((string) $appointment->program_plan_key);
        if (! $program) {
            throw new RuntimeException('Unknown program plan.');
        }
        if (($program->price_cents ?? 0) < self::MIN_AMOUNT_CENTS) {
            throw new RuntimeException('Stripe plan amount is too low. Set at least 100 cents in Programs.');
        }
        $stripePriceId = trim((string) ($program->stripe_price_id ?? ''));
        if ($stripePriceId === '') {
            throw new RuntimeException('Stripe subscription Price ID is missing for this program.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing in integration settings.');
        }

        Stripe::setApiKey($secretKey);

        $appUrl = rtrim((string) ($returnBaseUrl ?: config('app.url')), '/');

        return Session::create([
            'mode' => 'subscription',
            'success_url' => "{$appUrl}/?payment=success",
            'cancel_url' => "{$appUrl}/?payment=cancelled",
            'customer_email' => $appointment->email,
            'line_items' => [[
                'quantity' => 1,
                'price' => $stripePriceId,
            ]],
            'metadata' => [
                'appointment_id' => (string) $appointment->id,
                'program_plan_key' => (string) $appointment->program_plan_key,
            ],
        ]);
    }

    public function webhookSecret(): string
    {
        return (string) $this->settings->stripe('webhook_secret', '');
    }
}
