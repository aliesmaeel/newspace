<?php

namespace App\Filament\Resources\PageContents;

use App\Filament\Resources\PageContents\Pages\EditPageContent;
use App\Filament\Resources\PageContents\Pages\ListPageContents;
use App\Filament\Resources\PageContents\Schemas\PageContentForm;
use App\Models\PageContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageContentResource extends Resource
{
    protected static ?string $model = PageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = 'Homepage content';

    protected static ?string $modelLabel = 'Homepage content';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PageContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hero_title_line_1')->label('Hero title'),
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
            'index' => ListPageContents::route('/'),
            'edit' => EditPageContent::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
