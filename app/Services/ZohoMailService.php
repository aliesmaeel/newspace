<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Support\Facades\Http;

class ZohoMailService
{
    public function __construct(
        private ZohoOAuthService $oauth,
        private IntegrationSettingsService $settings
    ) {}

    public function sendHtml(string $to, string $subject, string $html): void
    {
        $mailBase = rtrim((string) $this->settings->zoho('mail_api_base', (string) config('services.zoho.mail_api_base')), '/');
        $fromAddress = (string) $this->settings->mailFromAddress();
        $fromName = (string) $this->settings->mailFromName();

        $token = $this->oauth->accessToken();
        $accountId = $this->resolveAccountId($token, $mailBase);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$mailBase}/api/accounts/{$accountId}/messages", [
                'fromAddress' => $fromAddress,
                'toAddress' => $to,
                'subject' => $subject,
                'content' => $html,
                'mailFormat' => 'html',
                'fromName' => $fromName,
            ]);

        if (! $response->ok()) {
            throw new RuntimeException('Zoho send failed: '.$response->body());
        }
    }

    private function resolveAccountId(string $token, string $mailBase): string
    {
        $configured = (string) $this->settings->zoho('mail_account_id', (string) config('services.zoho.mail_account_id'));
        if ($configured !== '') {
            return $configured;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$mailBase}/api/accounts");

        if (! $response->ok()) {
            throw new RuntimeException('Unable to resolve Zoho mail account ID: '.$response->body());
        }

        $payload = $response->json();

        $accounts = $payload['data'] ?? $payload['accounts'] ?? [];
        if (! is_array($accounts) || count($accounts) === 0) {
            throw new RuntimeException('No Zoho mail accounts found for this token.');
        }

        $first = $accounts[0];
        $id = (string) ($first['accountId'] ?? $first['mailAccountId'] ?? $first['id'] ?? '');
        if ($id === '') {
            throw new RuntimeException('Zoho account response did not include account ID.');
        }

        return $id;
    }
}
