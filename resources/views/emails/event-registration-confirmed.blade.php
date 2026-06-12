<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">You are registered for {{ $event->title }}</h2>

    <p>Hi {{ $user->name }},</p>
    <p>Thank you for registering. Your spot is confirmed. Here are the event details:</p>

    <ul>
        <li><strong>Event:</strong> {{ $event->title }}</li>
        @if ($event->starts_at)
            <li><strong>Starts:</strong> {{ $event->starts_at->format('l, F j, Y \a\t g:i A') }}</li>
        @endif
        @if ($event->ends_at)
            <li><strong>Ends:</strong> {{ $event->ends_at->format('l, F j, Y \a\t g:i A') }}</li>
        @endif
        <li><strong>Format:</strong> {{ $event->isVirtual() ? 'Virtual' : 'In person' }}</li>
        @if ($event->isPhysical() && $event->address)
            <li><strong>Location:</strong> {{ $event->address }}</li>
        @endif
        @if ($mapUrl)
            <li><strong>Map:</strong> <a href="{{ $mapUrl }}">{{ $mapUrl }}</a></li>
        @endif
        @if ($event->isVirtual() && filled($event->virtual_link))
            <li><strong>Meeting link:</strong> <a href="{{ $event->virtual_link }}">{{ $event->virtual_link }}</a></li>
        @endif
    </ul>

    <p>
        <a href="{{ $eventUrl }}">View event details on our website</a>
    </p>

    <p>Thank you,<br>{{ config('brand.name') }}</p>
</body>
</html>
