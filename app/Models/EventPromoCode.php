<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPromoCode extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'discount_percentage',
        'stripe_coupon_id',
        'max_uses',
        'uses_count',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'discount_percentage' => 'integer',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isFree(): bool
    {
        return (int) $this->discount_percentage >= 100;
    }

    public function discountedPriceCents(int $priceCents): int
    {
        $percentage = max(0, min(100, (int) $this->discount_percentage));

        return (int) round($priceCents * (100 - $percentage) / 100);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
