<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventPromoCode;
use App\Support\Money;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
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
        $priceCents = (int) ($this->getOwnerRecord()->price_cents ?? 0);

        return $schema->components([
            TextInput::make('code')
                ->required()
                ->maxLength(64)
                ->default(fn () => Str::upper(Str::random(8)))
                ->dehydrateStateUsing(fn ($state) => Str::upper(trim((string) $state))),
            TextInput::make('discount_percentage')
                ->label('Discount')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->default(100)
                ->required()
                ->suffix('%')
                ->live(debounce: 400)
                ->helperText('100% = free'),
            Placeholder::make('discounted_price_preview')
                ->label('Price after discount')
                ->content(function (callable $get) use ($priceCents): string {
                    if ($priceCents <= 0) {
                        return 'Free (event has no price set)';
                    }

                    $percentage = (int) ($get('discount_percentage') ?: 0);
                    $discounted = (new EventPromoCode(['discount_percentage' => $percentage]))
                        ->discountedPriceCents($priceCents);

                    if ($discounted <= 0) {
                        return 'Free (' . Money::formatCents($priceCents) . ' → ' . Money::formatCents(0) . ')';
                    }

                    return Money::formatCents($priceCents) . ' → ' . Money::formatCents($discounted);
                }),
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
                TextColumn::make('discount_percentage')->label('Discount')->suffix('%'),
                TextColumn::make('uses_count'),
                TextColumn::make('max_uses'),
                TextColumn::make('expires_at')->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()->label('Generate promo code'),
            ]);
    }
}
