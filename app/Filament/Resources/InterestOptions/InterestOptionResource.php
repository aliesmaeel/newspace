<?php

namespace App\Filament\Resources\InterestOptions;

use App\Filament\Resources\InterestOptions\Pages\ManageInterestOptions;
use App\Models\InterestOption;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class InterestOptionResource extends Resource
{
    protected static ?string $model = InterestOption::class;

    protected static ?string $navigationLabel = 'Interest options';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->required()->maxLength(255),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable(),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInterestOptions::route('/'),
        ];
    }
}
