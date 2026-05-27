<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'payment_status',
        'used_first_event_free',
        'event_promo_code_id',
        'stripe_checkout_session_id',
        'registered_at',
    ];

    protected $casts = [
        'used_first_event_free' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(EventPromoCode::class, 'event_promo_code_id');
    }
}
