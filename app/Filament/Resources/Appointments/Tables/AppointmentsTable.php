<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_at')
                    ->label('Appointment')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                BadgeColumn::make('status')
                    ->state(fn (Appointment $record): string => $record->display_status)
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'pending_payment',
                        'success' => 'approved',
                        'gray' => 'passed',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('program_plan_name')
                    ->label('Program')
                    ->toggleable(),
                TextColumn::make('google_meet_link')
                    ->label('Meet Link')
                    ->formatStateUsing(fn(?string $state): string => $state ? Str::limit($state, 30) : '-')
                    ->url(fn(Appointment $record): ?string => $record->google_meet_link ?: null)
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('addToGoogleCalendar')
                    ->label('Add to Calendar')
                    ->icon('heroicon-m-calendar')
                    ->color('info')
                    ->visible(fn(Appointment $record): bool => in_array($record->display_status, ['approved', 'passed'], true))
                    ->url(fn(Appointment $record): string => self::buildGoogleCalendarUrl($record), shouldOpenInNewTab: true),
                Action::make('reject')
                    ->visible(fn(Appointment $record): bool => $record->status !== 'rejected')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('admin_notes')
                            ->label('Reason / notes to customer')
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Appointment $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'approved_at' => null,
                            'approved_by' => Auth::id(),
                            'google_meet_link' => null,
                        ]);

                        Notification::make()
                            ->title('Appointment rejected and customer emailed.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function buildGoogleCalendarUrl(Appointment $record): string
    {
        $start = $record->appointment_at->copy()->utc()->format('Ymd\THis\Z');
        $end = $record->appointment_at->copy()->addMinutes(30)->utc()->format('Ymd\THis\Z');
        $text = urlencode("NeoSpace Appointment - {$record->first_name} {$record->last_name}");
        $details = urlencode(
            "Customer: {$record->first_name} {$record->last_name}\n"
                . "Email: {$record->email}\n"
                . "Phone: {$record->phone}\n"
                . "Message: " . ($record->message ?: 'N/A') . "\n"
                . "Google Meet: " . ($record->google_meet_link ?: 'N/A')
        );
        $location = urlencode($record->google_meet_link ?: 'Online meeting');

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&dates={$start}/{$end}&text={$text}&details={$details}&location={$location}";
    }
}
