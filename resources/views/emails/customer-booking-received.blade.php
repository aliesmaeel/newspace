<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">We received your booking</h2>

    <p>Hi {{ $appointment->first_name }},</p>
    <p>Thank you for booking with {{ config('brand.name') }}. Your request has been received and is pending review.</p>

    <ul>
        <li><strong>Date:</strong> {{ $appointment->appointment_at->format('D, M j, Y') }}</li>
        <li><strong>Time:</strong> {{ $appointment->appointment_at->format('g:i A') }}</li>
        @include('emails.partials.zoom-meeting-link')
    </ul>

    <p>You will receive another email once your appointment is confirmed.</p>

    <p>Thank you,<br>{{ config('brand.name') }}</p>
</body>
</html>
