<?php

namespace App\Filament\Resources\IntegrationSettings\Pages;

use App\Filament\Resources\IntegrationSettings\IntegrationSettingResource;
use App\Models\IntegrationSetting;
use Filament\Resources\Pages\ListRecords;

class ListIntegrationSettings extends ListRecords
{
    protected static string $resource = IntegrationSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = IntegrationSetting::query()->firstOrCreate(
            ['id' => 1],
            IntegrationSetting::defaultsFromEnv()
        );

        $this->redirect(IntegrationSettingResource::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
