<?php

namespace App\Filament\Resources\IntegrationSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntegrationSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meetings')
                    ->description('Shared link included in booking emails to customers and admins.')
                    ->schema([
                        TextInput::make('zoom_meeting_url')
                            ->label('Zoom meeting URL')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://zoom.us/j/…')
                            ->helperText('Leave empty to use ZOOM_MEETING_URL from the server environment when set.'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Section::make('Stripe Settings')
                    ->schema([
                        TextInput::make('stripe_publishable_key')
                            ->required()
                            ->disabled(),
                        TextInput::make('stripe_secret_key')
                            ->password()
                            ->revealable()
                            ->required()
                            ->disabled(),
                        TextInput::make('stripe_webhook_secret')
                            ->password()
                            ->revealable()
                            ->required()
                            ->disabled(),
                        TextInput::make('stripe_price_12_weeks')
                            ->numeric()
                            ->minValue(100)
                            ->required()
                            ->live(onBlur: true)
                            ->helperText(fn($state): string => self::priceHelperText($state)),
                        TextInput::make('stripe_price_6_months')
                            ->numeric()
                            ->minValue(100)
                            ->required()
                            ->live(onBlur: true)
                            ->helperText(fn($state): string => self::priceHelperText($state)),
                        TextInput::make('stripe_price_1_year')
                            ->numeric()
                            ->minValue(100)
                            ->required()
                            ->live(onBlur: true)
                            ->helperText(fn($state): string => self::priceHelperText($state)),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function priceHelperText($state): string
    {
        $base = 'Amount in cents. Minimum 100.';
        if (! is_numeric($state)) {
            return "{$base} Example: 50000 = $500.00";
        }

        $cents = (int) $state;
        $amount = number_format($cents / 100, 2);

        return "{$base} Current: {$cents} = \${$amount}";
    }
}
