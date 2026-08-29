<?php

namespace App\Filament\Resources\MissionPageContents\Pages;

use App\Filament\Resources\MissionPageContents\MissionPageContentResource;
use Filament\Resources\Pages\EditRecord;

class EditMissionPageContent extends EditRecord
{
    protected static string $resource = MissionPageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
