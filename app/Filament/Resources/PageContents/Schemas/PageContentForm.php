<?php

namespace App\Filament\Resources\PageContents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero section')
                    ->description('Top banner on the homepage.')
                    ->schema([
                        FileUpload::make('hero_banner_path')
                            ->label('Banner image')
                            ->disk('public')
                            ->directory('page-banners')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->helperText('Leave empty to use the default homepage banner image.')
                            ->columnSpanFull(),
                        TextInput::make('hero_eyebrow')->label('Eyebrow')->maxLength(255)->columnSpanFull(),
                        TextInput::make('hero_title_line_1')->label('Title line 1')->maxLength(255),
                        TextInput::make('hero_title_line_2')->label('Title line 2')->maxLength(255),
                        Textarea::make('hero_lead')->label('Lead text')->rows(4)->columnSpanFull(),
                        TextInput::make('hero_cta_text')->label('Button text')->maxLength(255),
                        TextInput::make('hero_cta_link')->label('Button link')->maxLength(255)->placeholder('/booking'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('About — introduction')
                    ->description('Opening about section on the homepage.')
                    ->schema([
                        TextInput::make('about_eyebrow')
                            ->label('Eyebrow')
                            ->maxLength(255)
                            ->placeholder('Leave empty to use “About {brand name}”.')
                            ->columnSpanFull(),
                        Textarea::make('about_heading')->label('Heading')->rows(2)->columnSpanFull(),
                        Textarea::make('about_lead')
                            ->label('Lead text')
                            ->rows(4)
                            ->helperText('One line per paragraph.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — who we serve')
                    ->schema([
                        TextInput::make('who_eyebrow')->label('Eyebrow')->maxLength(255),
                        Textarea::make('who_heading')->label('Heading')->rows(2)->columnSpanFull(),
                        Textarea::make('who_lead')->label('Lead text')->rows(3)->columnSpanFull(),
                        Repeater::make('audiences')
                            ->label('Audience cards')
                            ->schema([
                                TextInput::make('title')->required()->maxLength(255),
                                Textarea::make('body')->required()->rows(3),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — belief & research')
                    ->schema([
                        TextInput::make('belief_eyebrow')->label('Belief eyebrow')->maxLength(255),
                        Textarea::make('belief_lead')->label('Belief text')->rows(4)->columnSpanFull(),
                        TextInput::make('why_now_eyebrow')->label('Why now eyebrow')->maxLength(255),
                        Textarea::make('why_now_heading')->label('Why now heading')->rows(2)->columnSpanFull(),
                        Repeater::make('why_now_insights')
                            ->label('Research insights')
                            ->schema([
                                Textarea::make('text')->required()->rows(3),
                            ])
                            ->columnSpanFull(),
                        Textarea::make('why_now_source')->label('Research source')->rows(2)->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — transformations & process')
                    ->schema([
                        TextInput::make('transform_eyebrow')->label('Transform eyebrow')->maxLength(255),
                        Repeater::make('transforms')
                            ->label('Transformations')
                            ->schema([
                                TextInput::make('from')->label('From')->required()->maxLength(255),
                                TextInput::make('to')->label('To')->required()->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        TextInput::make('process_eyebrow')->label('Process eyebrow')->maxLength(255),
                        Textarea::make('process_heading')->label('Process heading')->rows(2)->columnSpanFull(),
                        Repeater::make('process_steps')
                            ->label('Process steps')
                            ->schema([
                                TextInput::make('label')->required()->maxLength(255),
                                TextInput::make('title')->required()->maxLength(255),
                                Textarea::make('body')->required()->rows(3),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — why SAKOUR & regions')
                    ->schema([
                        TextInput::make('why_eyebrow')->label('Why eyebrow')->maxLength(255),
                        Textarea::make('why_tagline')->label('Tagline')->rows(2)->columnSpanFull(),
                        Textarea::make('why_paragraph_1')->label('Paragraph 1')->rows(3)->columnSpanFull(),
                        Textarea::make('why_paragraph_2')->label('Paragraph 2')->rows(3)->columnSpanFull(),
                        Textarea::make('why_paragraph_3')->label('Paragraph 3')->rows(3)->columnSpanFull(),
                        TextInput::make('regions_eyebrow')->label('Regions eyebrow')->maxLength(255),
                        Textarea::make('regions_lead')->label('Regions lead')->rows(3)->columnSpanFull(),
                        Repeater::make('regions')
                            ->label('Region cards')
                            ->schema([
                                TextInput::make('title')->required()->maxLength(255),
                                Textarea::make('body')->required()->rows(3),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — founder')
                    ->schema([
                        TextInput::make('founder_eyebrow')->label('Eyebrow')->maxLength(255),
                        TextInput::make('founder_name')->label('Name')->maxLength(255),
                        TextInput::make('founder_role')
                            ->label('Role')
                            ->maxLength(255)
                            ->placeholder('Leave empty to use “Founder, {brand name}”.'),
                        Textarea::make('founder_hook')->label('Hook line')->rows(2)->columnSpanFull(),
                        Textarea::make('founder_paragraph_1')->label('Paragraph 1')->rows(3)->columnSpanFull(),
                        Textarea::make('founder_paragraph_2')->label('Paragraph 2')->rows(3)->columnSpanFull(),
                        Textarea::make('founder_paragraph_3')->label('Paragraph 3')->rows(3)->columnSpanFull(),
                        Repeater::make('founder_credentials')
                            ->label('Credentials')
                            ->simple(
                                TextInput::make('credential')->required()->maxLength(255),
                            )
                            ->columnSpanFull(),
                        Textarea::make('founder_quote')->label('Quote')->rows(3)->columnSpanFull(),
                        TextInput::make('founder_quote_footer')->label('Quote footer')->maxLength(255)->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('About — call to action')
                    ->schema([
                        TextInput::make('cta_eyebrow')->label('Eyebrow')->maxLength(255),
                        Textarea::make('cta_heading')->label('Heading')->rows(2)->columnSpanFull(),
                        Textarea::make('cta_body')->label('Body')->rows(3)->columnSpanFull(),
                        TextInput::make('cta_button_text')->label('Button text')->maxLength(255),
                        TextInput::make('cta_button_link')->label('Button link')->maxLength(255)->placeholder('/contact'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
