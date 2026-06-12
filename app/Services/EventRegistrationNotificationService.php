<?php

namespace App\Services;

use App\Mail\EventRegistrationConfirmedMail;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EventRegistrationNotificationService
{
    public function sendConfirmation(EventRegistration $registration): void
    {
        $registration->refresh();

        if ($registration->status !== 'confirmed' || $registration->payment_status !== 'paid') {
            return;
        }

        if ($registration->confirmation_email_sent_at !== null) {
            return;
        }

        $claimed = EventRegistration::query()
            ->whereKey($registration->id)
            ->whereNull('confirmation_email_sent_at')
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->update(['confirmation_email_sent_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $registration->refresh();
        $registration->loadMissing(['event', 'user']);

        if (! $registration->event || ! $registration->user?->email) {
            EventRegistration::query()
                ->whereKey($registration->id)
                ->update(['confirmation_email_sent_at' => null]);

            return;
        }

        try {
            Mail::to($registration->user->email)->send(new EventRegistrationConfirmedMail($registration));
        } catch (Throwable $e) {
            EventRegistration::query()
                ->whereKey($registration->id)
                ->update(['confirmation_email_sent_at' => null]);

            report($e);
        }
    }
}
