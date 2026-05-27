<?php

namespace App\Services;

use App\Models\Event;
use RuntimeException;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeEventSyncService
{
    public function __construct(private IntegrationSettingsService $settings) {}

    public function sync(Event $event): Event
    {
        if ((int) $event->price_cents < 100) {
            throw new RuntimeException('Set a price of at least 100 pence before syncing to Stripe.');
        }

        $secretKey = (string) $this->settings->stripe('secret_key');
        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret key is missing in Integration Settings.');
        }

        Stripe::setApiKey($secretKey);

        $productId = trim((string) ($event->stripe_product_id ?? ''));
        if ($productId === '') {
            $product = Product::create([
                'name' => $event->title,
                'metadata' => [
                    'event_id' => (string) $event->id,
                    'event_slug' => (string) $event->slug,
                ],
            ]);
            $productId = $product->id;
        } else {
            Product::update($productId, ['name' => $event->title]);
        }

        $previousPriceId = trim((string) ($event->stripe_price_id ?? ''));

        $price = Price::create([
            'product' => $productId,
            'unit_amount' => (int) $event->price_cents,
            'currency' => config('services.stripe.currency', 'gbp'),
        ]);

        if ($previousPriceId !== '' && $previousPriceId !== $price->id) {
            try {
                Price::update($previousPriceId, ['active' => false]);
            } catch (\Throwable) {
                report($previousPriceId);
            }
        }

        $event->update([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $price->id,
        ]);

        return $event->fresh();
    }
}
