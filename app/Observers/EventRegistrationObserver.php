<?php

namespace App\Observers;

use App\Models\EventRegistration;
use App\Models\EventRegistrationHistory;
use Throwable;

class EventRegistrationObserver
{
    /**
     * Snapshot the registration into the history table once it is confirmed.
     * The snapshot keeps the event name/date even if the event is later deleted.
     */
    public function saved(EventRegistration $registration): void
    {
        if ($registration->status !== 'confirmed') {
            return;
        }

        try {
            $registration->loadMissing(['event.eventType', 'user']);
            $event = $registration->event;
            $user = $registration->user;

            EventRegistrationHistory::updateOrCreate(
                ['event_registration_id' => $registration->id],
                [
                    'event_id' => $registration->event_id,
                    'event_title' => $event?->title ?? 'Deleted event',
                    'event_type' => $event?->eventType?->name,
                    'event_type_id' => $event?->event_type_id,
                    'event_starts_at' => $event?->starts_at,
                    'event_location_type' => $event?->location_type,
                    'user_id' => $registration->user_id,
                    'user_name' => $user?->name,
                    'user_email' => $user?->email,
                    'status' => $registration->status,
                    'payment_status' => $registration->payment_status,
                    'amount_cents' => (int) ($event?->price_cents ?? 0),
                    'registered_at' => $registration->registered_at ?? now(),
                ],
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
