<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventOccurrence extends Model
{
    protected $fillable = [
        'event_id',
        'label',
        'location_type',
        'address',
        'latitude',
        'longitude',
        'virtual_link',
        'price_cents',
        'stripe_product_id',
        'stripe_price_id',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'price_cents' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'sort_order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(EventPromoCode::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendees(): HasMany
    {
        return $this->registrations()->where('status', 'confirmed');
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

    public function locationLabel(): string
    {
        return match ($this->location_type) {
            'virtual' => 'Virtual',
            default => 'In person',
        };
    }

    public function displayLabel(): string
    {
        if (filled($this->label)) {
            return (string) $this->label;
        }

        if ($this->isPhysical() && filled($this->address)) {
            return (string) $this->address;
        }

        return $this->locationLabel();
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
