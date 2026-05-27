<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Services\StripeProgramSyncService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncToStripe')
                ->label('Sync to Stripe')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sync to Stripe')
                ->modalDescription('Creates or updates the product in Stripe and adds a recurring price for the amount and billing interval on this program.')
                ->visible(fn (): bool => (int) $this->getRecord()->price_cents >= 100)
                ->action(function (): void {
                    try {
                        $program = app(StripeProgramSyncService::class)->sync($this->getRecord());
                        $this->record = $program;
                        $this->refreshFormData([
                            'stripe_product_id',
                            'stripe_price_id',
                            'billing_interval_months',
                            'price_cents',
                        ]);

                        Notification::make()
                            ->title('Synced to Stripe')
                            ->body("Product {$program->stripe_product_id} · Price {$program->stripe_price_id}")
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
            DeleteAction::make(),
        ];
    }
}
