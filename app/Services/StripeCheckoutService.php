<?php

namespace App\Services;

use App\Models\Appointment;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService
{
    private const MIN_AMOUNT_CENTS = 100;

    public function __construct(private IntegrationSettingsService $settings) {}

    public function planCatalog(): array
    {
        return [
            'twelve-weeks' => [
                'name' => '12 Weeks Commitment',
                'amount' => (int) ($this->settings->stripe('price_12_weeks', 50000)),
            ],
            'six-months' => [
                'name' => '6 Months Commitment',
                'amount' => (int) ($this->settings->stripe('price_6_months', 120000)),
            ],
            'one-year' => [
                'name' => '1 Year Commitment',
                'amount' => (int) ($this->settings->stripe('price_1_year', 240000)),
            ],
        ];
    }

    public function createSession(Appointment $appointment, ?string $returnBaseUrl = null): Session
    {
        $catalog = $this->planCatalog();
        $plan = $catalog[$appointment->program_plan_key] ?? null;
        if (! $plan) {
            throw new RuntimeException('Unknown program plan.');
        }
        if (($plan['amount'] ?? 0) < self::MIN_AMOUNT_CENTS) {
            throw new RuntimeException('Stripe plan amount is too low. Set at least 100 cents in Integration Settings.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing in integration settings.');
        }

        Stripe::setApiKey($secretKey);

        $appUrl = rtrim((string) ($returnBaseUrl ?: config('app.url')), '/');

        return Session::create([
            'mode' => 'payment',
            'success_url' => "{$appUrl}/?payment=success",
            'cancel_url' => "{$appUrl}/?payment=cancelled",
            'customer_email' => $appointment->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $plan['amount'],
                    'product_data' => [
                        'name' => $plan['name'],
                        'description' => "Program booking for {$appointment->first_name} {$appointment->last_name}",
                    ],
                ],
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
