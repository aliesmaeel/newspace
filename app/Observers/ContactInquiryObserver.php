<?php

namespace App\Observers;

use App\Models\ContactInquiry;
use App\Models\User;
use App\Notifications\NewContactInquiryCreated;
use Throwable;

class ContactInquiryObserver
{
    public function created(ContactInquiry $inquiry): void
    {
        try {
            User::query()
                ->where('is_admin', true)
                ->each(function (User $user) use ($inquiry): void {
                    $user->notify(new NewContactInquiryCreated($inquiry));
                });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
