<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactInquiry extends EditRecord
{
    protected static string $resource = ContactInquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->read_at === null) {
            $this->record->update(['read_at' => now()]);
            $this->record->refresh();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
