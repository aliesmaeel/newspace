<?php

namespace App\Filament\Resources\IntegrationSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntegrationSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branding')
                    ->description('Logo and founder photo shown on the public website.')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Site logo')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->helperText('Recommended: PNG or SVG with transparent background. Leave empty to use the default logo.'),
                        FileUpload::make('founder_photo_path')
                            ->label('Founder photo')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions([
                                null,
                                '1:1',
                                '3:4',
                                '4:5',
                            ])
                            ->imageAspectRatio('3:4')
                            ->openable()
                            ->downloadable()
                            ->helperText('Shown on the About page founder section. Use the editor to crop before saving. Leave empty to use the default photo.'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
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
                Section::make('Stripe')
                    ->description('Keys from your Stripe Dashboard → Developers → API keys. Switching between test and live keys means you should re-sync programs and event dates to Stripe.')
                    ->schema([
                        TextInput::make('stripe_publishable_key')
                            ->label('Publishable key')
                            ->placeholder('pk_live_… or pk_test_…')
                            ->helperText('Starts with pk_. Used by checkout on the website.')
                            ->required()
                            ->maxLength(255)
                            ->startsWith('pk_')
                            ->autocomplete(false)
                            ->copyable()
                            ->columnSpanFull(),
                        TextInput::make('stripe_secret_key')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->placeholder('sk_live_… or sk_test_…')
                            ->helperText('Starts with sk_. Keep this private. Used to create checkout sessions and sync products.')
                            ->required()
                            ->startsWith('sk_')
                            ->autocomplete('new-password')
                            ->columnSpanFull(),
                        TextInput::make('stripe_webhook_secret')
                            ->label('Webhook signing secret')
                            ->password()
                            ->revealable()
                            ->placeholder('whsec_…')
                            ->helperText('Starts with whsec_. From the webhook endpoint that points at /api/stripe/webhook.')
                            ->required()
                            ->startsWith('whsec_')
                            ->autocomplete('new-password')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
