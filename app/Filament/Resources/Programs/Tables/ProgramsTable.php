<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Models\Program;
use App\Services\StripeProgramSyncService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn ($state, Program $record): string => $record->formattedPriceLabel()),
                TextColumn::make('billing_interval_months')
                    ->label('Billing')
                    ->formatStateUsing(fn (?int $state): string => $state
                        ? (Program::billingIntervalMonthLabels()[$state] ?? "Every {$state} months")
                        : '-')
                    ->toggleable(),
                IconColumn::make('stripe_price_id')
                    ->label('Stripe')
                    ->boolean()
                    ->getStateUsing(fn (Program $record): bool => $record->isSyncedWithStripe())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('syncToStripe')
                    ->label('Sync to Stripe')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Program $record): bool => (int) $record->price_cents >= 100)
                    ->action(function (Program $record): void {
                        try {
                            $program = app(StripeProgramSyncService::class)->sync($record);

                            Notification::make()
                                ->title('Synced to Stripe')
                                ->body("Price {$program->stripe_price_id}")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Stripe sync failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
