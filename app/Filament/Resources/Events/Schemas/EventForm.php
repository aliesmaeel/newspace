<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if ((string) $get('slug') === '') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                RichEditor::make('description')->columnSpanFull(),
                FileUpload::make('image_url')->label('Event image')->disk('public')->directory('events')->image()->columnSpanFull(),
                Select::make('location_type')->options(['physical' => 'Physical', 'virtual' => 'Virtual'])->required()->default('physical'),
                TextInput::make('address')->maxLength(500)->columnSpanFull(),
                TextInput::make('latitude')->numeric(),
                TextInput::make('longitude')->numeric(),
                TextInput::make('virtual_link')->url()->maxLength(2048)->columnSpanFull(),
                TextInput::make('price_cents')->label('Price (pence)')->numeric()->default(0)->required(),
                DateTimePicker::make('starts_at')->required(),
                DateTimePicker::make('ends_at'),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
                Toggle::make('is_active')->default(true)->required(),
            ])
            ->columns(2);
    }
}
