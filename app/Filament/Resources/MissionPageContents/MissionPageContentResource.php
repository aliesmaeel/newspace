<?php

namespace App\Filament\Resources\MissionPageContents;

use App\Filament\Resources\MissionPageContents\Pages\EditMissionPageContent;
use App\Filament\Resources\MissionPageContents\Pages\ListMissionPageContents;
use App\Filament\Resources\MissionPageContents\Schemas\MissionPageContentForm;
use App\Models\PageContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MissionPageContentResource extends Resource
{
    protected static ?string $model = PageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Heart;

    protected static ?string $navigationLabel = 'Mission page';

    protected static ?string $modelLabel = 'Mission page';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MissionPageContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mission_heading')->label('Heading'),
                TextColumn::make('updated_at')->dateTime(),
            ])
            ->recordActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMissionPageContents::route('/'),
            'edit' => EditMissionPageContent::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
