<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;

    public ?EventOccurrence $occurrence;

    public User $user;

    public string $eventUrl;

    public ?string $mapUrl;

    public function __construct(public EventRegistration $registration)
    {
        $registration->loadMissing(['event', 'occurrence', 'user']);
        $this->event = $registration->event;
        $this->occurrence = $registration->occurrence;
        $this->user = $registration->user;
        $this->eventUrl = rtrim((string) config('app.url'), '/') . '/events/' . $this->event->slug
            . ($this->occurrence ? '?occurrence=' . $this->occurrence->id : '');
        $this->mapUrl = $this->occurrence?->mapUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are registered: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-registration-confirmed',
        );
    }
}
