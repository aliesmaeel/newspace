<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(50),
                DateTimePicker::make('appointment_at')
                    ->required()
                    ->seconds(false),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_payment' => 'Pending Payment',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'passed' => 'Passed',
                    ])
                    ->required(),
                TextInput::make('google_meet_link')
                    ->url()
                    ->maxLength(255),
                Textarea::make('message')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
