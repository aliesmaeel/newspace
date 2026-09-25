<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventOccurrence;
use App\Models\EventPromoCode;
use App\Services\StripeEventSyncService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Throwable;

class OccurrencesRelationManager extends RelationManager
{
    protected static string $relationship = 'occurrences';

    protected static ?string $title = 'Dates & locations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')
                ->label('Label')
                ->maxLength(255)
                ->helperText('Optional short name, e.g. London or Paris.')
                ->columnSpanFull(),
            Select::make('location_type')
                ->options(['physical' => 'Physical', 'virtual' => 'Virtual'])
                ->required()
                ->default('physical')
                ->live(),
            TextInput::make('address')->maxLength(500)->columnSpanFull()
                ->visible(fn (callable $get): bool => $get('location_type') !== 'virtual'),
            TextInput::make('latitude')->numeric()
                ->visible(fn (callable $get): bool => $get('location_type') !== 'virtual'),
            TextInput::make('longitude')->numeric()
                ->visible(fn (callable $get): bool => $get('location_type') !== 'virtual'),
            TextInput::make('virtual_link')->url()->maxLength(2048)->columnSpanFull()
                ->visible(fn (callable $get): bool => $get('location_type') === 'virtual'),
            TextInput::make('price_cents')->label('Price (pence)')->numeric()->default(0)->required(),
            DateTimePicker::make('starts_at')->required(),
            DateTimePicker::make('ends_at'),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
            Toggle::make('is_active')->default(true)->required(),
            Repeater::make('promoCodes')
                ->relationship()
                ->label('Promo codes')
                ->schema([
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
                        ->content(function (callable $get): string {
                            $priceCents = (int) ($get('../../price_cents') ?: 0);
                            if ($priceCents <= 0) {
                                return 'Free (session has no price set)';
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
                ])
                ->columns(2)
                ->columnSpanFull()
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    $data['event_id'] = $this->getOwnerRecord()->getKey();

                    return $data;
                })
                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                    $data['event_id'] = $this->getOwnerRecord()->getKey();

                    return $data;
                }),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('label')->placeholder('—')->searchable(),
                TextColumn::make('starts_at')->dateTime('M j, Y g:i A')->sortable(),
                TextColumn::make('location_type')->badge(),
                TextColumn::make('address')->limit(30)->placeholder('—')->toggleable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn ($state, EventOccurrence $record) => $record->formattedPriceLabel()),
                TextColumn::make('promo_codes_count')->counts('promoCodes')->label('Promos'),
                IconColumn::make('stripe_price_id')
                    ->label('Stripe')
                    ->boolean()
                    ->getStateUsing(fn (EventOccurrence $record): bool => filled($record->stripe_price_id)),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('Add date / location'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('syncToStripe')
                    ->label('Sync Stripe')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (EventOccurrence $record): void {
                        try {
                            app(StripeEventSyncService::class)->sync($record);
                            Notification::make()->title('Synced to Stripe')->success()->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Stripe sync failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
                DeleteAction::make(),
            ]);
    }
}
