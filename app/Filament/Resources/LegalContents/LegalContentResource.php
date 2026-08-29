<?php

namespace App\Filament\Resources\LegalContents;

use App\Filament\Resources\LegalContents\Pages\EditLegalContent;
use App\Filament\Resources\LegalContents\Pages\ListLegalContents;
use App\Filament\Resources\LegalContents\Schemas\LegalContentForm;
use App\Models\LegalContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LegalContentResource extends Resource
{
    protected static ?string $model = LegalContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?string $navigationLabel = 'Legal & privacy';

    protected static ?string $modelLabel = 'Legal & privacy';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LegalContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('controller_name')->label('Controller'),
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
            'index' => ListLegalContents::route('/'),
            'edit' => EditLegalContent::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
