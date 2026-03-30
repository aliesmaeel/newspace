<?php

namespace App\Filament\Resources\IntegrationSettings\Pages;

use App\Filament\Resources\IntegrationSettings\IntegrationSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditIntegrationSetting extends EditRecord
{
    protected static string $resource = IntegrationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
