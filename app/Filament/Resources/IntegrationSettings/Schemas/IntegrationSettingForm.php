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
                    ->description('Logo shown in the website header and admin dashboard.')
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
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
