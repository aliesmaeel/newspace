<?php

namespace App\Services;

use App\Models\IntegrationSetting;

class IntegrationSettingsService
{
    public function get(): IntegrationSetting
    {
        return IntegrationSetting::query()->firstOrCreate(
            ['id' => 1],
            IntegrationSetting::defaultsFromEnv()
        );
    }

    public function zoho(string $key, ?string $fallback = null): ?string
    {
        $settings = $this->get();
        $value = match ($key) {
            'account_hosted' => $settings->zoho_account_hosted,
            'accounts_base' => $settings->zoho_accounts_base,
            'mail_api_base' => $settings->zoho_mail_api_base,
            'client_id' => $settings->zoho_client_id,
            'client_secret' => $settings->zoho_client_secret,
            'redirect_uri' => $settings->zoho_redirect_uri,
            'scope' => $settings->zoho_scope,
            'mail_account_id' => $settings->zoho_mail_account_id,
            default => null,
        };

        return $value ?: $fallback;
    }

    public function mailAdminAddress(): ?string
    {
        $addr = config('mail.admin_address');

        return is_string($addr) && $addr !== '' ? $addr : null;
    }

    /**
     * Zoom join URL for booking emails. Dashboard value overrides config / .env when non-empty.
     */
    public function zoomMeetingUrl(): string
    {
        $fromDb = trim((string) ($this->get()->zoom_meeting_url ?? ''));
        if ($fromDb !== '') {
            return $fromDb;
        }

        return trim((string) config('meetings.zoom_meeting_url', ''));
    }

    public function stripe(string $key, mixed $fallback = null): mixed
    {
        $settings = $this->get();
        $value = match ($key) {
            'publishable_key' => $settings->stripe_publishable_key,
            'secret_key' => $settings->stripe_secret_key,
            'webhook_secret' => $settings->stripe_webhook_secret,
            default => null,
        };

        return $value ?? $fallback;
    }

    public function logoUrl(): string
    {
        $path = trim((string) ($this->get()->logo_path ?? ''));

        return self::resolvePublicAssetUrl($path, '/assets/neospace-logo.png');
    }

    public static function resolvePublicAssetUrl(string $path, string $fallback): string
    {
        $path = trim($path);
        if ($path === '') {
            return $fallback;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return $path;
        }

        return '/storage/' . ltrim($path, '/');
    }
}
