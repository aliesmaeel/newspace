<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('appointment_email')
                    ->label('Appointment Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gateway')
                    ->badge(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state, $record): string => strtoupper((string) $record->currency).' '.number_format($state / 100, 2)),
                TextColumn::make('external_id')
                    ->label('Checkout Session')
                    ->copyable()
                    ->limit(24),
                TextColumn::make('payment_intent_id')
                    ->label('Payment Intent')
                    ->copyable()
                    ->limit(24),
                TextColumn::make('paid_at')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
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
