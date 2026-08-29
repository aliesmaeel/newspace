<?php

namespace App\Filament\Resources\PageContents\Pages;

use App\Filament\Resources\PageContents\PageContentResource;
use App\Models\PageContent;
use Filament\Resources\Pages\ListRecords;

class ListPageContents extends ListRecords
{
    protected static string $resource = PageContentResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = PageContent::query()->firstOrCreate(
            ['id' => 1],
            PageContent::defaults()
        );

        $this->redirect(PageContentResource::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
