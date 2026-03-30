<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'zoho_account_hosted',
        'zoho_accounts_base',
        'zoho_mail_api_base',
        'zoho_client_id',
        'zoho_client_secret',
        'zoho_redirect_uri',
        'zoho_scope',
        'zoho_mail_account_id',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_price_12_weeks',
        'stripe_price_6_months',
        'stripe_price_1_year',
        'zoom_meeting_url',
    ];

    protected $casts = [
        'zoho_client_secret' => 'encrypted',
        'stripe_secret_key' => 'encrypted',
        'stripe_webhook_secret' => 'encrypted',
        'stripe_price_12_weeks' => 'integer',
        'stripe_price_6_months' => 'integer',
        'stripe_price_1_year' => 'integer',
    ];

    public static function defaultsFromEnv(): array
    {
        return [
            'zoho_account_hosted' => (string) env('ZOHO_ACCOUNT_HOSTED', 'zoho.eu'),
            'zoho_accounts_base' => (string) env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.eu'),
            'zoho_mail_api_base' => (string) env('ZOHO_MAIL_API_BASE', 'https://mail.zoho.eu'),
            'zoho_client_id' => (string) env('ZOHO_CLIENT_ID'),
            'zoho_client_secret' => (string) env('ZOHO_CLIENT_SECRET'),
            'zoho_redirect_uri' => (string) env('ZOHO_REDIRECT_URI'),
            'zoho_scope' => (string) env('ZOHO_SCOPE', 'ZohoMail.messages.CREATE,offline_access'),
            'zoho_mail_account_id' => (string) env('ZOHO_MAIL_ACCOUNT_ID'),
            'stripe_publishable_key' => (string) env('STRIPE_PUBLISHABLE_KEY'),
            'stripe_secret_key' => (string) env('STRIPE_SECRET_KEY'),
            'stripe_webhook_secret' => (string) env('STRIPE_WEBHOOK_SECRET'),
            'stripe_price_12_weeks' => env('STRIPE_PRICE_12_WEEKS'),
            'stripe_price_6_months' => env('STRIPE_PRICE_6_MONTHS'),
            'stripe_price_1_year' => env('STRIPE_PRICE_1_YEAR'),
            'zoom_meeting_url' => (string) env('ZOOM_MEETING_URL', ''),
        ];
    }
}
