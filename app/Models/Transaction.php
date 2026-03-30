<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'appointment_email',
        'gateway',
        'type',
        'status',
        'amount_cents',
        'currency',
        'external_id',
        'payment_intent_id',
        'payload',
        'paid_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];
}
