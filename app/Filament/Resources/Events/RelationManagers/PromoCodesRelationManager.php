<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PromoCodesRelationManager extends RelationManager
{
    protected static string $relationship = 'promoCodes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->maxLength(64)
                ->default(fn () => Str::upper(Str::random(8)))
                ->dehydrateStateUsing(fn ($state) => Str::upper(trim((string) $state))),
            TextInput::make('max_uses')->numeric()->nullable()->helperText('Leave empty for unlimited uses.'),
            DateTimePicker::make('expires_at'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->copyable(),
                TextColumn::make('uses_count'),
                TextColumn::make('max_uses'),
                TextColumn::make('expires_at')->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()->label('Generate promo code'),
            ]);
    }
}
