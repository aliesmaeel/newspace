<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">You are registered for {{ $event->title }}</h2>

    <p>Hi {{ $user->name }},</p>
    <p>Thank you for registering. Your spot is confirmed. Here are the event details:</p>

    <ul>
        <li><strong>Event:</strong> {{ $event->title }}</li>
        @if ($occurrence?->label)
            <li><strong>Session:</strong> {{ $occurrence->label }}</li>
        @endif
        @if ($occurrence?->starts_at)
            <li><strong>Starts:</strong> {{ $occurrence->starts_at->format('l, F j, Y \a\t g:i A') }}</li>
        @endif
        @if ($occurrence?->ends_at)
            <li><strong>Ends:</strong> {{ $occurrence->ends_at->format('l, F j, Y \a\t g:i A') }}</li>
        @endif
        <li><strong>Format:</strong> {{ $occurrence?->isVirtual() ? 'Virtual' : 'In person' }}</li>
        @if ($occurrence?->isPhysical() && $occurrence->address)
            <li><strong>Location:</strong> {{ $occurrence->address }}</li>
        @endif
        @if ($mapUrl)
            <li><strong>Map:</strong> <a href="{{ $mapUrl }}">{{ $mapUrl }}</a></li>
        @endif
        @if ($occurrence?->isVirtual() && filled($occurrence->virtual_link))
            <li><strong>Meeting link:</strong> <a href="{{ $occurrence->virtual_link }}">{{ $occurrence->virtual_link }}</a></li>
        @endif
    </ul>

    <p>
        <a href="{{ $eventUrl }}">View event details on our website</a>
    </p>

    <p>Thank you,<br>{{ config('brand.name') }}</p>
</body>
</html>
