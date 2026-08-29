<?php

namespace App\Filament\Resources\LegalContents\Pages;

use App\Filament\Resources\LegalContents\LegalContentResource;
use Filament\Resources\Pages\EditRecord;

class EditLegalContent extends EditRecord
{
    protected static string $resource = LegalContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
