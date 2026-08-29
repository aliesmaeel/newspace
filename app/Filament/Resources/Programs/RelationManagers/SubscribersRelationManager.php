<?php

namespace App\Filament\Resources\Programs\RelationManagers;

use App\Models\Appointment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscribersRelationManager extends RelationManager
{
    protected static string $relationship = 'subscribers';

    protected static ?string $title = 'Subscribers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')
                    ->label('First name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Last name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->toggleable(),
                TextColumn::make('appointment_at')
                    ->label('Appointment')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->state(fn (Appointment $record): string => $record->display_status)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'pending_payment' => 'info',
                        'passed' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
