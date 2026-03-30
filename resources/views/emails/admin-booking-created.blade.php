<!DOCTYPE html>
<html lang="en">

<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">New appointment booking</h2>

    @if ($appointment->status === 'approved' && ! $appointment->requires_payment)
        <p>A customer booked a free appointment. It has been automatically confirmed.</p>
    @elseif ($appointment->status === 'approved' && $appointment->requires_payment && $appointment->payment_status === 'paid')
        <p>A customer completed payment. The appointment is confirmed. A shared Zoom link is listed below when configured; you can still add a per-appointment Google Meet link in the dashboard.</p>
    @else
        <p>A customer has created a new appointment request.</p>
    @endif

    <ul>
        <li><strong>Name:</strong> {{ $appointment->first_name }} {{ $appointment->last_name }}</li>
        <li><strong>Email:</strong> {{ $appointment->email }}</li>
        <li><strong>Phone:</strong> {{ $appointment->phone }}</li>
        <li><strong>Date:</strong> {{ $appointment->appointment_at->format('D, M j, Y') }}</li>
        <li><strong>Time:</strong> {{ $appointment->appointment_at->format('g:i A') }}</li>
        <li><strong>Message:</strong> {{ $appointment->message ?: 'N/A' }}</li>
        @include('emails.partials.zoom-meeting-link')
    </ul>
    <p>
        <a href="{{ rtrim(config('app.url'), '/') }}/admin/appointments?search={{ $appointment->email }}">Open dashboard</a>
    </p>
</body>

</html>