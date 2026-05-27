<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.55;">
    <h2 style="margin-bottom: 12px;">Welcome to {{ config('brand.name') }}</h2>

    <p>Hi {{ $user->name }},</p>
    <p>Thank you for registering with {{ config('brand.name') }}. We are glad to have you with us.</p>
    <p>Please confirm your email address by clicking the button below. This link expires in 60 minutes.</p>

    <p style="margin: 24px 0;">
        <a href="{{ $verificationUrl }}"
           style="display: inline-block; background: #2a2418; color: #f0e5c3; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Verify email address
        </a>
    </p>

    <p style="font-size: 14px; color: #6b7280;">
        If the button does not work, copy and paste this link into your browser:<br>
        <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
    </p>

    <p>If you did not create an account, you can ignore this email.</p>

    <p>Thank you,<br>{{ config('brand.name') }}</p>
</body>
</html>
