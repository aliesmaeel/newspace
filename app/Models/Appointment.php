<?php

namespace App\Models;

use App\Mail\CustomerBookingApprovedMail;
use App\Mail\CustomerBookingRejectedMail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Appointment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PASSED = 'passed';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
        'program_plan_key',
        'program_plan_name',
        'requires_payment',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'appointment_at',
        'appointment_date',
        'appointment_time',
        'status',
        'google_meet_link',
        'admin_notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
        'appointment_date' => 'date',
        'requires_payment' => 'boolean',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updated(function (Appointment $appointment): void {
            if ($appointment->wasChanged('status') && $appointment->status === 'approved') {
                try {
                    Mail::to($appointment->email)->send(new CustomerBookingApprovedMail($appointment));
                } catch (Throwable $e) {
                    report($e);
                }
            }
            if ($appointment->wasChanged('status') && $appointment->status === 'rejected') {
                try {
                    Mail::to($appointment->email)->send(new CustomerBookingRejectedMail($appointment));
                } catch (Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === self::STATUS_APPROVED && $this->appointment_at?->isPast()) {
            return self::STATUS_PASSED;
        }

        if ($this->status === self::STATUS_PENDING_PAYMENT) {
            return self::STATUS_PENDING_PAYMENT;
        }

        return $this->status;
    }
}
