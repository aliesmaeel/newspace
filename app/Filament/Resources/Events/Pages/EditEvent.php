<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Services\StripeEventSyncService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncToStripe')
                ->label('Sync to Stripe')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => (int) $this->getRecord()->price_cents >= 100)
                ->action(function (): void {
                    try {
                        $event = app(StripeEventSyncService::class)->sync($this->getRecord());
                        $this->record = $event;
                        Notification::make()->title('Synced to Stripe')->success()->send();
                    } catch (Throwable $e) {
                        Notification::make()->title('Stripe sync failed')->body($e->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
