<?php

namespace App\Filament\Resources\MissionPageContents\Pages;

use App\Filament\Resources\MissionPageContents\MissionPageContentResource;
use App\Models\PageContent;
use Filament\Resources\Pages\ListRecords;

class ListMissionPageContents extends ListRecords
{
    protected static string $resource = MissionPageContentResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = PageContent::query()->firstOrCreate(
            ['id' => 1],
            PageContent::defaults()
        );

        $this->redirect(MissionPageContentResource::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
