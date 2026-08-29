<?php

namespace App\Notifications;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Illuminate\Notifications\Notification;

class NewContactInquiryCreated extends Notification
{
    public function __construct(private ContactInquiry $inquiry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $name = trim("{$this->inquiry->first_name} {$this->inquiry->last_name}");

        return [
            'title' => 'New contact inquiry',
            'body' => "{$name} submitted: {$this->inquiry->subject}",
            'status' => 'info',
            'duration' => 'persistent',
            'format' => 'filament',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'View inquiry',
                    'url' => ContactInquiryResource::getUrl('edit', ['record' => $this->inquiry]),
                    'markAsRead' => true,
                ],
            ],
        ];
    }
}
