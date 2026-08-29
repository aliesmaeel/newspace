<?php

namespace App\Filament\Resources\LegalContents\Pages;

use App\Filament\Resources\LegalContents\LegalContentResource;
use App\Models\LegalContent;
use Filament\Resources\Pages\ListRecords;

class ListLegalContents extends ListRecords
{
    protected static string $resource = LegalContentResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = LegalContent::query()->firstOrCreate(
            ['id' => 1],
            LegalContent::defaults()
        );

        $this->redirect(LegalContentResource::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
