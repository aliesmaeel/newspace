<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">Appointment confirmed</h2>

    <p>Hi {{ $appointment->first_name }},</p>
    <p>Your appointment has been approved and confirmed.</p>

    <ul>
        <li><strong>Date:</strong> {{ $appointment->appointment_at->format('D, M j, Y') }}</li>
        <li><strong>Time:</strong> {{ $appointment->appointment_at->format('g:i A') }}</li>
        @if ($appointment->google_meet_link)
            <li><strong>Google Meet:</strong> <a href="{{ $appointment->google_meet_link }}">{{ $appointment->google_meet_link }}</a></li>
        @endif
        @include('emails.partials.zoom-meeting-link')
    </ul>

    <p>Thank you,<br>NeoSpace Leadership Global</p>
</body>
</html>
