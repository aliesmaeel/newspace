<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\NewAppointmentCreated;
use Throwable;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        try {
            User::query()->each(function (User $user) use ($appointment): void {
                $user->notify(new NewAppointmentCreated($appointment));
            });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
