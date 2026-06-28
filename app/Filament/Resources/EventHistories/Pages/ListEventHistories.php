<?php

namespace App\Filament\Resources\EventHistories\Pages;

use App\Filament\Resources\EventHistories\EventHistoryResource;
use App\Filament\Resources\EventHistories\Widgets\EventHistoryStats;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListEventHistories extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = EventHistoryResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            EventHistoryStats::class,
        ];
    }
}
