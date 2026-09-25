<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'event_type_id',
        'description',
        'image_url',
        'is_active',
        'first_time_free',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_time_free' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(EventOccurrence::class)->orderBy('starts_at')->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendees(): HasMany
    {
        return $this->registrations()->where('status', 'confirmed');
    }

    public function promoCodes(): HasManyThrough
    {
        return $this->hasManyThrough(EventPromoCode::class, EventOccurrence::class);
    }

    public function upcomingOccurrences(): HasMany
    {
        return $this->occurrences()
            ->where('is_active', true)
            ->where('starts_at', '>=', now()->subDay());
    }
}
