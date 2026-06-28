<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('eventType.name')->label('Type')->badge()->placeholder('—'),
                TextColumn::make('starts_at')->dateTime('M j, Y g:i A')->sortable(),
                TextColumn::make('location_type')->badge(),
                TextColumn::make('price_cents')->label('Price')->formatStateUsing(fn ($state, $record) => $record->formattedPriceLabel()),
                IconColumn::make('stripe_price_id')->label('Stripe')->boolean()->getStateUsing(fn ($record) => filled($record->stripe_price_id)),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
