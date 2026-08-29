<?php

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Models\ContactInquiry;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')
                    ->label('First name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Last name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),
                IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->getStateUsing(fn (ContactInquiry $record): bool => $record->read_at !== null),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('View'),
                DeleteAction::make(),
            ]);
    }
}
