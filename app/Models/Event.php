<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'event_type_id',
        'description',
        'image_url',
        'location_type',
        'address',
        'latitude',
        'longitude',
        'virtual_link',
        'price_cents',
        'stripe_product_id',
        'stripe_price_id',
        'billing_interval_months',
        'starts_at',
        'ends_at',
        'is_active',
        'first_time_free',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'first_time_free' => 'boolean',
        'price_cents' => 'integer',
        'billing_interval_months' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'sort_order' => 'integer',
    ];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(EventPromoCode::class);
    }

    public function formattedPriceLabel(): string
    {
        if ((int) $this->price_cents <= 0) {
            return 'Free';
        }

        return Money::formatCents((int) $this->price_cents);
    }

    public function isPhysical(): bool
    {
        return $this->location_type === 'physical';
    }

    public function isVirtual(): bool
    {
        return $this->location_type === 'virtual';
    }

    public function mapUrl(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return 'https://www.google.com/maps?q=' . $this->latitude . ',' . $this->longitude;
        }

        if (filled($this->address)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode((string) $this->address);
        }

        return null;
    }
}
