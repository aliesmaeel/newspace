<?php

namespace App\Filament\Resources\LegalContents\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data controller')
                    ->description('Legal identity shown in your privacy policy.')
                    ->schema([
                        TextInput::make('controller_name')
                            ->label('Controller name')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('controller_address')
                            ->label('UK address')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('privacy_contact_email')
                            ->label('Privacy contact email')
                            ->email()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Privacy policy')
                    ->description('Full privacy policy shown on the public /privacy page.')
                    ->schema([
                        RichEditor::make('privacy_policy_html')
                            ->label('Privacy policy')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Cookie policy')
                    ->description('Cookie policy shown on the public /cookies page.')
                    ->schema([
                        RichEditor::make('cookie_policy_html')
                            ->label('Cookie policy')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Cookie notice')
                    ->description('Short text for the first-visit banner on the website.')
                    ->schema([
                        Textarea::make('cookie_notice_text')
                            ->label('Banner text')
                            ->rows(3)
                            ->helperText('Keep this to one or two sentences. Links to the cookie and privacy pages are added automatically.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
