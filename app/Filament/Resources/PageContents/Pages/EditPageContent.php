<?php

namespace App\Filament\Resources\PageContents\Pages;

use App\Filament\Resources\PageContents\PageContentResource;
use Filament\Resources\Pages\EditRecord;

class EditPageContent extends EditRecord
{
    protected static string $resource = PageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
