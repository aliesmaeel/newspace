<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class NewAppointmentCreated extends Notification
{
    public function __construct(private Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New appointment received',
            'body' => "{$this->appointment->first_name} {$this->appointment->last_name} created a booking request.",
            'status' => 'success',
            'duration' => 'persistent',
            'format' => 'filament',
        ];
    }
}
