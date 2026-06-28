<?php

namespace App\Filament\Resources\EventHistories\Tables;

use App\Models\EventRegistrationHistory;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('registered_at', 'desc')
            ->columns([
                TextColumn::make('event_title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->description(fn (EventRegistrationHistory $record): ?string => $record->event_id ? null : 'Event deleted'),
                TextColumn::make('event_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('user_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('event_starts_at')
                    ->label('Event date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('event_location_type')
                    ->label('Location')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'confirmed',
                        'warning' => 'pending_payment',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? 'GBP '.number_format($state / 100, 2) : 'Free')
                    ->sortable(),
                TextColumn::make('registered_at')
                    ->label('Registered')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'pending_payment' => 'Pending payment',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(fn (): array => EventRegistrationHistory::query()
                        ->whereNotNull('event_id')
                        ->orderBy('event_title')
                        ->pluck('event_title', 'event_id')
                        ->all())
                    ->searchable(),
                SelectFilter::make('event_type')
                    ->label('Event type')
                    ->options(fn (): array => EventRegistrationHistory::query()
                        ->whereNotNull('event_type')
                        ->distinct()
                        ->orderBy('event_type')
                        ->pluck('event_type', 'event_type')
                        ->all())
                    ->searchable(),
                SelectFilter::make('timeslot')
                    ->label('Timeslot')
                    ->options(fn (): array => self::timeslotOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query
                            ->whereNotNull('event_starts_at')
                            ->whereRaw('TIME(event_starts_at) = ?', [$value]);
                    }),
                Filter::make('event_date')
                    ->schema([
                        DatePicker::make('from')->label('Event from'),
                        DatePicker::make('until')->label('Event until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('event_starts_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('event_starts_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'Event from '.Carbon::parse($data['from'])->toFormattedDateString();
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Event until '.Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Build timeslot options from the distinct event start times present in the history.
     *
     * @return array<string, string>
     */
    private static function timeslotOptions(): array
    {
        return EventRegistrationHistory::query()
            ->whereNotNull('event_starts_at')
            ->selectRaw('DISTINCT TIME(event_starts_at) as slot')
            ->orderBy('slot')
            ->pluck('slot')
            ->filter()
            ->mapWithKeys(fn (string $slot): array => [
                $slot => Carbon::createFromFormat('H:i:s', $slot)->format('g:i A'),
            ])
            ->all();
    }
}
