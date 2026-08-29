<?php

namespace App\Filament\Resources\ContactInquiries\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')->disabled(),
                TextInput::make('last_name')->disabled(),
                TextInput::make('email')->disabled()->copyable(),
                TextInput::make('phone')->disabled()->copyable(),
                TextInput::make('subject')->disabled()->columnSpanFull(),
                Textarea::make('question')
                    ->label('Message')
                    ->disabled()
                    ->rows(8)
                    ->columnSpanFull(),
                Placeholder::make('submitted_at')
                    ->label('Submitted')
                    ->content(fn ($record): string => $record?->created_at?->format('M j, Y g:i A') ?? '—'),
                Placeholder::make('read_at')
                    ->label('Read in dashboard')
                    ->content(fn ($record): string => $record?->read_at?->format('M j, Y g:i A') ?? 'Not yet'),
            ])
            ->columns(2);
    }
}
