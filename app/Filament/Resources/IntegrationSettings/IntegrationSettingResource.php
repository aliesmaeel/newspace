<?php

namespace App\Filament\Resources\IntegrationSettings;

use App\Filament\Resources\IntegrationSettings\Pages\EditIntegrationSetting;
use App\Filament\Resources\IntegrationSettings\Pages\ListIntegrationSettings;
use App\Filament\Resources\IntegrationSettings\Schemas\IntegrationSettingForm;
use App\Filament\Resources\IntegrationSettings\Tables\IntegrationSettingsTable;
use App\Models\IntegrationSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IntegrationSettingResource extends Resource
{
    protected static ?string $model = IntegrationSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static ?string $navigationLabel = 'Configuration';

    protected static string|UnitEnum|null $navigationGroup = 'Developers';

    public static function form(Schema $schema): Schema
    {
        return IntegrationSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntegrationSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntegrationSettings::route('/'),
            'edit' => EditIntegrationSetting::route('/{record}/edit'),
        ];
    }
}
