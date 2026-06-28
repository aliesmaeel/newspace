<?php

namespace App\Filament\Resources\EventHistories;

use App\Filament\Resources\EventHistories\Pages\ListEventHistories;
use App\Filament\Resources\EventHistories\Tables\EventHistoriesTable;
use App\Models\EventRegistrationHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EventHistoryResource extends Resource
{
    protected static ?string $model = EventRegistrationHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|UnitEnum|null $navigationGroup = 'Booking';

    protected static ?string $navigationLabel = 'Event History';

    protected static ?string $modelLabel = 'event registration';

    protected static ?string $pluralModelLabel = 'event history';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return EventHistoriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventHistories::route('/'),
        ];
    }
}
