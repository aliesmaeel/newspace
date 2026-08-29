<?php

namespace App\Filament\Resources\MissionPageContents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MissionPageContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mission banner')
                    ->description('Hero banner at the top of the SAKOUR Mission page.')
                    ->schema([
                        FileUpload::make('mission_banner_path')
                            ->label('Banner image')
                            ->disk('public')
                            ->directory('page-banners')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->helperText('Leave empty to use the default giving-back background image.')
                            ->columnSpanFull(),
                        TextInput::make('mission_eyebrow')->label('Eyebrow')->maxLength(255)->columnSpanFull(),
                        TextInput::make('mission_heading')->label('Heading')->maxLength(255)->columnSpanFull(),
                        Textarea::make('mission_paragraph_1')->label('Paragraph 1')->rows(4)->columnSpanFull(),
                        Textarea::make('mission_paragraph_2')->label('Paragraph 2')->rows(4)->columnSpanFull(),
                        Textarea::make('mission_closing')->label('Closing line')->rows(2)->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Mission call to action')
                    ->description('Card below the banner.')
                    ->schema([
                        TextInput::make('mission_cta_eyebrow')->label('Eyebrow')->maxLength(255),
                        Textarea::make('mission_cta_heading')->label('Heading')->rows(2)->columnSpanFull(),
                        Textarea::make('mission_cta_body')->label('Body')->rows(3)->columnSpanFull(),
                        TextInput::make('mission_cta_button_text')->label('Button text')->maxLength(255),
                        TextInput::make('mission_cta_button_link')->label('Button link')->maxLength(255)->placeholder('/booking'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
