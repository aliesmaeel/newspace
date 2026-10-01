<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $set('slug', Str::slug((string) $state));
                    }),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)
                    ->helperText('Auto-generated from the title. You can still edit it.'),
                Select::make('event_type_id')
                    ->label('Event type')
                    ->relationship('eventType', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(255),
                    ]),
                self::descriptionEditor(),
                FileUpload::make('image_url')->label('Event image')->disk('public')->directory('events')->image()->columnSpanFull(),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
                Toggle::make('is_active')->default(true)->required(),
                Toggle::make('first_time_free')
                    ->label('First visit of this type is free')
                    ->default(false)
                    ->helperText("If on, a customer's first event of this type is free."),
            ])
            ->columns(2);
    }

    private static function descriptionEditor(): RichEditor
    {
        return RichEditor::make('description')
            ->label('Description')
            ->placeholder('Describe the event: who it is for, the agenda, and what to expect.')
            ->helperText('Headings, colors, images, tables, columns, and collapsible sections are available. Click inside a table to edit rows and columns.')
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link', 'textColor', 'highlight'],
                [
                    ToolbarButtonGroup::make('Headings', ['paragraph', 'h2', 'h3', 'h4'])->textualButtons(),
                    ToolbarButtonGroup::make('Alignment', ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify']),
                ],
                ['blockquote', 'bulletList', 'orderedList', 'horizontalRule', 'details'],
                ['table', 'attachFiles', 'grid', 'gridDelete'],
                ['small', 'lead', 'clearFormatting', 'undo', 'redo'],
            ])
            ->floatingToolbars([
                'table' => [
                    'tableAddColumnBefore',
                    'tableAddColumnAfter',
                    'tableDeleteColumn',
                    'tableAddRowBefore',
                    'tableAddRowAfter',
                    'tableDeleteRow',
                    'tableMergeCells',
                    'tableSplitCell',
                    'tableToggleHeaderRow',
                    'tableToggleHeaderCell',
                    'tableDelete',
                ],
            ])
            ->textColors([
                'gold' => TextColor::make('Gold', '#b6994c', darkColor: '#d2b463'),
                'cream' => TextColor::make('Cream', '#ccc5b1', darkColor: '#f2f2f0'),
                'muted' => TextColor::make('Muted', '#6b6456', darkColor: '#9a9180'),
                'dark' => TextColor::make('Dark', '#141311', darkColor: '#1d1b18'),
                'white' => TextColor::make('White', '#f8f6f0', darkColor: '#ffffff'),
                ...TextColor::getDefaults(),
            ])
            ->customTextColors()
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('events/content')
            ->fileAttachmentsVisibility('public')
            ->fileAttachmentsMaxSize(5120)
            ->resizableImages()
            ->columnSpanFull();
    }
}
