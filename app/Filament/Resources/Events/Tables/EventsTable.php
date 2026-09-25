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
            ->defaultSort('sort_order')
            ->modifyQueryUsing(fn ($query) => $query->with(['occurrences', 'eventType']))
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('eventType.name')->label('Type')->badge()->placeholder('—'),
                TextColumn::make('occurrences_count')
                    ->label('Sessions')
                    ->counts('occurrences')
                    ->sortable(),
                TextColumn::make('next_starts_at')
                    ->label('Next session')
                    ->getStateUsing(function ($record) {
                        $next = $record->occurrences
                            ->where('is_active', true)
                            ->filter(fn ($o) => $o->starts_at && $o->starts_at->gte(now()->subDay()))
                            ->sortBy('starts_at')
                            ->first();

                        return $next?->starts_at;
                    })
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('—'),
                TextColumn::make('attendees_count')
                    ->label('Attendees')
                    ->counts('attendees')
                    ->sortable(),
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
