<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        $slug = (string) $get('slug');
                        if ($slug === '') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Used in booking links, for example: /booking?plan=this-slug'),
                RichEditor::make('description')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->helperText('You can format text and add bullet points.')
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->label('Program image')
                    ->disk('public')
                    ->directory('programs')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable()
                    ->helperText('Upload an image. Existing image will be shown while editing.')
                    ->columnSpanFull(),
                TextInput::make('price_cents')
                    ->label('Price (pence)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required()
                    ->live(onBlur: true)
                    ->helperText(fn ($state): string => self::priceHelperText($state)),
                Select::make('billing_interval_months')
                    ->label('Billing interval')
                    ->options(Program::billingIntervalMonthLabels())
                    ->nullable()
                    ->helperText('How often Stripe charges the customer (e.g. 3 = every 3 months). Use Sync to Stripe after saving.'),
                Placeholder::make('stripe_sync_status')
                    ->label('Stripe')
                    ->content(function (?Program $record): HtmlString {
                        if (! $record) {
                            return new HtmlString('<span class="text-gray-500">Save the program, then use <strong>Sync to Stripe</strong>.</span>');
                        }

                        if ($record->isSyncedWithStripe()) {
                            return new HtmlString(
                                '<div class="space-y-1 text-sm">'
                                . '<div><strong>Product:</strong> ' . e($record->stripe_product_id) . '</div>'
                                . '<div><strong>Price:</strong> ' . e($record->stripe_price_id) . '</div>'
                                . '<div class="text-success-600">Synced. Re-sync after changing price or billing interval.</div>'
                                . '</div>'
                            );
                        }

                        return new HtmlString('<span class="text-warning-600">Not synced yet. Click <strong>Sync to Stripe</strong> in the page header.</span>');
                    })
                    ->columnSpanFull()
                    ->visible(fn (?Program $record): bool => $record !== null),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }

    private static function priceHelperText($state): string
    {
        if (! is_numeric($state)) {
            return 'Use pence/cents. Example: 120000 = £1,200.00. Set 0 for free programs.';
        }

        $cents = (int) $state;

        return 'Current: ' . $cents . ' = ' . \App\Support\Money::formatCents($cents) . '. Set 0 for free programs.';
    }
}
