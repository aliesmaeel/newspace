<?php

namespace App\Services;

use App\Models\Program;
use RuntimeException;
use Illuminate\Support\Str;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeProgramSyncService
{
    private const MIN_AMOUNT_CENTS = 100;

    public function __construct(private IntegrationSettingsService $settings) {}

    public function sync(Program $program): Program
    {
        if ((int) $program->price_cents < self::MIN_AMOUNT_CENTS) {
            throw new RuntimeException('Set a price of at least 100 cents before syncing to Stripe.');
        }

        $intervalMonths = (int) $program->billing_interval_months;
        if (! in_array($intervalMonths, Program::billingIntervalMonthOptions(), true)) {
            throw new RuntimeException('Choose a billing interval (1, 2, 3, 6, or 12 months) before syncing.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing in Integration Settings.');
        }

        Stripe::setApiKey($secretKey);

        $productId = trim((string) ($program->stripe_product_id ?? ''));
        $plainDescription = Str::limit(trim(strip_tags((string) $program->description)), 500, '');

        if ($productId === '') {
            $product = Product::create([
                'name' => $program->title,
                'description' => $plainDescription !== '' ? $plainDescription : null,
                'metadata' => [
                    'program_id' => (string) $program->id,
                    'program_slug' => (string) $program->slug,
                ],
            ]);
            $productId = $product->id;
        } else {
            Product::update($productId, [
                'name' => $program->title,
                'description' => $plainDescription !== '' ? $plainDescription : null,
            ]);
        }

        $previousPriceId = trim((string) ($program->stripe_price_id ?? ''));

        $price = Price::create([
            'product' => $productId,
            'unit_amount' => (int) $program->price_cents,
            'currency' => config('services.stripe.currency', 'gbp'),
            'recurring' => [
                'interval' => 'month',
                'interval_count' => $intervalMonths,
            ],
            'metadata' => [
                'program_id' => (string) $program->id,
                'program_slug' => (string) $program->slug,
                'billing_interval_months' => (string) $intervalMonths,
            ],
        ]);

        if ($previousPriceId !== '' && $previousPriceId !== $price->id) {
            try {
                Price::update($previousPriceId, ['active' => false]);
            } catch (\Throwable) {
                report(new RuntimeException("Could not deactivate previous Stripe price {$previousPriceId}."));
            }
        }

        $program->update([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $price->id,
        ]);

        return $program->fresh();
    }
}
