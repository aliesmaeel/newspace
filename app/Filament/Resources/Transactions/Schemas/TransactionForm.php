<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('appointment_email')->disabled(),
                TextInput::make('gateway')->disabled(),
                TextInput::make('type')->disabled(),
                TextInput::make('status')->disabled(),
                TextInput::make('amount_cents')->disabled(),
                TextInput::make('currency')->disabled(),
                TextInput::make('external_id')->disabled(),
                TextInput::make('payment_intent_id')->disabled(),
                TextInput::make('paid_at')->disabled(),
                Textarea::make('payload')
                    ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '')
                    ->rows(12)
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
