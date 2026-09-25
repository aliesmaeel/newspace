<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Attendees';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('registered_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.phone')
                    ->label('Phone')
                    ->toggleable(),
                TextColumn::make('occurrence.starts_at')
                    ->label('Session')
                    ->dateTime('M j, Y g:i A')
                    ->description(fn ($record) => $record->occurrence?->displayLabel())
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending_payment' => 'warning',
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
                TextColumn::make('promoCode.code')
                    ->label('Promo code')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('used_first_event_free')
                    ->label('First-time free')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('registered_at')
                    ->label('Registered')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_occurrence_id')
                    ->label('Session')
                    ->relationship('occurrence', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->starts_at?->format('M j, Y g:i A') . ' — ' . $record->displayLabel())
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
