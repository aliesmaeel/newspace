<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistrationHistory extends Model
{
    protected $table = 'event_registration_histories';

    protected $fillable = [
        'event_registration_id',
        'event_id',
        'event_title',
        'event_type',
        'event_type_id',
        'event_starts_at',
        'event_location_type',
        'user_id',
        'user_name',
        'user_email',
        'status',
        'payment_status',
        'amount_cents',
        'registered_at',
    ];

    protected $casts = [
        'event_starts_at' => 'datetime',
        'registered_at' => 'datetime',
        'amount_cents' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
