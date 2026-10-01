<?php

namespace App\Services;

use App\Models\EventOccurrence;
use App\Support\StripeCurrentMode;
use RuntimeException;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeEventSyncService
{
    public function __construct(private IntegrationSettingsService $settings) {}

    public function sync(EventOccurrence $occurrence): EventOccurrence
    {
        if ((int) $occurrence->price_cents < 100) {
            throw new RuntimeException('Set a price of at least 100 pence before syncing to Stripe.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing in Integration Settings.');
        }

        Stripe::setApiKey($secretKey);

        $occurrence->loadMissing('event');
        $event = $occurrence->event;
        if (! $event) {
            throw new RuntimeException('Occurrence is missing its parent event.');
        }

        $productName = $event->title;
        if (filled($occurrence->label)) {
            $productName .= ' — '.$occurrence->label;
        } elseif ($occurrence->starts_at) {
            $productName .= ' — '.$occurrence->starts_at->format('j M Y');
        }

        $productId = StripeCurrentMode::existingProductId($occurrence->stripe_product_id);
        if ($productId === null) {
            $product = Product::create([
                'name' => $productName,
                'metadata' => [
                    'event_id' => (string) $event->id,
                    'event_slug' => (string) $event->slug,
                    'event_occurrence_id' => (string) $occurrence->id,
                ],
            ]);
            $productId = $product->id;
        } else {
            Product::update($productId, ['name' => $productName]);
        }

        $previousPriceId = trim((string) ($occurrence->stripe_price_id ?? ''));

        $price = Price::create([
            'product' => $productId,
            'unit_amount' => (int) $occurrence->price_cents,
            'currency' => config('services.stripe.currency', 'gbp'),
        ]);

        if ($previousPriceId !== '' && $previousPriceId !== $price->id) {
            try {
                Price::update($previousPriceId, ['active' => false]);
            } catch (\Throwable) {
                report($previousPriceId);
            }
        }

        $occurrence->update([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $price->id,
        ]);

        return $occurrence->fresh();
    }
}
