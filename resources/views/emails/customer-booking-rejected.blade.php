<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">Appointment request update</h2>

    <p>Hi {{ $appointment->first_name }},</p>
    <p>
        Thank you for your booking request. At this time, we are unable to confirm this appointment slot.
    </p>

    <ul>
        <li><strong>Requested date:</strong> {{ $appointment->appointment_at->format('D, M j, Y') }}</li>
        <li><strong>Requested time:</strong> {{ $appointment->appointment_at->format('g:i A') }}</li>
        @if ($appointment->admin_notes)
            <li><strong>Note:</strong> {{ $appointment->admin_notes }}</li>
        @endif
    </ul>

    <p>Please book another slot or reply to this email for support.</p>
    <p>Thank you,<br>NeoSpace Leadership Global</p>
</body>
</html>
