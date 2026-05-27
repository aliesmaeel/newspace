<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'image_url',
        'price_cents',
        'stripe_product_id',
        'stripe_price_id',
        'billing_interval_months',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'billing_interval_months' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function billingIntervalMonthOptions(): array
    {
        return [1, 2, 3, 6, 12];
    }

    public static function billingIntervalMonthLabels(): array
    {
        return [
            1 => 'Every 1 month',
            2 => 'Every 2 months',
            3 => 'Every 3 months',
            6 => 'Every 6 months',
            12 => 'Every 12 months',
        ];
    }

    public function formattedPriceLabel(): string
    {
        if ((int) $this->price_cents <= 0) {
            return 'Free';
        }

        $amount = Money::formatCents((int) $this->price_cents);
        $months = (int) $this->billing_interval_months;

        if ($months === 1) {
            return "{$amount} / month";
        }

        if ($months > 1) {
            return "{$amount} / {$months} months";
        }

        return $amount;
    }

    public function isSyncedWithStripe(): bool
    {
        return trim((string) $this->stripe_price_id) !== ''
            && trim((string) $this->stripe_product_id) !== '';
    }
}
