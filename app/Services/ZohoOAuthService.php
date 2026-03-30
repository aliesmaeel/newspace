<?php

namespace App\Services;

use App\Models\ZohoToken;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZohoOAuthService
{
    public function __construct(private IntegrationSettingsService $settings) {}

    public function authorizeUrl(): string
    {
        $base = rtrim((string) $this->settings->zoho('accounts_base', (string) config('services.zoho.accounts_base')), '/');
        $clientId = (string) $this->settings->zoho('client_id', (string) config('services.zoho.client_id'));
        $redirectUri = (string) $this->settings->zoho('redirect_uri', (string) config('services.zoho.redirect_uri'));
        $scope = (string) $this->settings->zoho('scope', (string) config('services.zoho.scope'));

        $query = http_build_query([
            'scope' => $scope,
            'client_id' => $clientId,
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'redirect_uri' => $redirectUri,
        ]);

        return "{$base}/oauth/v2/auth?{$query}";
    }

    public function exchangeCode(string $code): ZohoToken
    {
        $base = rtrim((string) $this->settings->zoho('accounts_base', (string) config('services.zoho.accounts_base')), '/');

        $response = Http::asForm()->post("{$base}/oauth/v2/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $this->settings->zoho('client_id', (string) config('services.zoho.client_id')),
            'client_secret' => $this->settings->zoho('client_secret', (string) config('services.zoho.client_secret')),
            'redirect_uri' => $this->settings->zoho('redirect_uri', (string) config('services.zoho.redirect_uri')),
            'code' => $code,
        ]);

        if (! $response->ok()) {
            throw new RuntimeException('Zoho token exchange failed: '.$response->body());
        }

        $payload = $response->json();

        return $this->persistTokens($payload);
    }

    public function accessToken(): string
    {
        $token = ZohoToken::query()->firstWhere('provider', 'zoho');

        if (! $token) {
            throw new RuntimeException('Zoho token is missing. Authorize the integration first.');
        }

        if ($token->isExpired()) {
            $token = $this->refresh($token);
        }

        if (! $token->access_token) {
            throw new RuntimeException('Zoho access token is empty.');
        }

        return $token->access_token;
    }

    public function refresh(ZohoToken $token): ZohoToken
    {
        if (! $token->refresh_token) {
            throw new RuntimeException('Zoho refresh token is missing. Reconnect Zoho integration.');
        }

        $base = rtrim((string) $this->settings->zoho('accounts_base', (string) config('services.zoho.accounts_base')), '/');
        $response = Http::asForm()->post("{$base}/oauth/v2/token", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->settings->zoho('client_id', (string) config('services.zoho.client_id')),
            'client_secret' => $this->settings->zoho('client_secret', (string) config('services.zoho.client_secret')),
            'refresh_token' => $token->refresh_token,
        ]);

        if (! $response->ok()) {
            throw new RuntimeException('Zoho token refresh failed: '.$response->body());
        }

        $payload = $response->json();
        if (! isset($payload['refresh_token'])) {
            $payload['refresh_token'] = $token->refresh_token;
        }

        return $this->persistTokens($payload);
    }

    private function persistTokens(array $payload): ZohoToken
    {
        $expiresIn = (int) ($payload['expires_in'] ?? $payload['expires_in_sec'] ?? 3600);

        return ZohoToken::query()->updateOrCreate(
            ['provider' => 'zoho'],
            [
                'access_token' => $payload['access_token'] ?? null,
                'refresh_token' => $payload['refresh_token'] ?? null,
                'token_type' => $payload['token_type'] ?? null,
                'expires_at' => now()->addSeconds($expiresIn),
            ],
        );
    }
}
