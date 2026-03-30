<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);

        $mailPort = (int) config('mail.mailers.smtp.port');
        $schemeRaw = config('mail.mailers.smtp.scheme');
        $scheme = self::normalizeSmtpScheme(is_string($schemeRaw) ? $schemeRaw : null, $mailPort);

        if ($scheme === null) {
            $scheme = $mailPort === 465 ? 'smtps' : 'smtp';
        }

        config(['mail.mailers.smtp.scheme' => $scheme]);
    }

    /**
     * Symfony Mailer only allows smtp / smtps. Legacy env values like "tls" must be mapped.
     */
    private static function normalizeSmtpScheme(?string $scheme, int $port): ?string
    {
        $s = $scheme !== null && $scheme !== '' ? strtolower(trim($scheme)) : '';

        if ($s === '') {
            return null;
        }

        return match ($s) {
            'smtp' => 'smtp',
            'smtps', 'ssl' => 'smtps',
            'tls', 'starttls', 'encrypt' => $port === 465 ? 'smtps' : 'smtp',
            default => null,
        };
    }
}
