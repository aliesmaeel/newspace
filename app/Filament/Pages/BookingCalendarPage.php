<?php

namespace App\Filament\Pages;

use App\Models\BookingUnavailability;
use App\Support\BookingTimeSlots;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BookingCalendarPage extends Page
{
    protected static ?string $slug = 'booking-calendar';

    protected static ?string $title = 'Booking availability';

    protected static ?string $navigationLabel = 'Availability calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 2;

    public int $calendarMonth;

    public int $calendarYear;

    public ?string $editingDate = null;

    public bool $editFullDay = false;

    /** @var array<int, string> */
    public array $editSlots = [];

    public bool $showEditor = false;

    public function mount(): void
    {
        $this->calendarMonth = (int) now()->month;
        $this->calendarYear = (int) now()->year;
    }

    public function updatedEditFullDay(mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->editSlots = [];
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.pages.booking-calendar'),
            ]);
    }

    public function previousMonth(): void
    {
        $d = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarYear = (int) $d->year;
        $this->calendarMonth = (int) $d->month;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarYear = (int) $d->year;
        $this->calendarMonth = (int) $d->month;
    }

    /**
     * @return list<list<?Carbon>>
     */
    public function getCalendarWeeks(): array
    {
        $first = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfDay();
        $startDow = ($first->dayOfWeek + 6) % 7;
        $daysInMonth = $first->daysInMonth;
        $cells = [];
        for ($i = 0; $i < $startDow; $i++) {
            $cells[] = null;
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cells[] = Carbon::create($this->calendarYear, $this->calendarMonth, $d)->startOfDay();
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }

    public function dayState(?Carbon $day): string
    {
        if ($day === null) {
            return 'empty';
        }
        $ymd = $day->toDateString();
        if (BookingUnavailability::query()->whereDate('blocked_date', $ymd)->where('is_full_day', true)->exists()) {
            return 'full';
        }
        if (BookingUnavailability::query()->whereDate('blocked_date', $ymd)->where('is_full_day', false)->exists()) {
            return 'partial';
        }

        return 'open';
    }

    public function openEditor(string $dateYmd): void
    {
        $this->editingDate = $dateYmd;
        $full = BookingUnavailability::query()
            ->whereDate('blocked_date', $dateYmd)
            ->where('is_full_day', true)
            ->exists();
        $this->editFullDay = $full;
        $this->editSlots = $full ? [] : BookingUnavailability::blockedSlotLabelsForDate($dateYmd);
        $this->showEditor = true;
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->editingDate = null;
        $this->editFullDay = false;
        $this->editSlots = [];
    }

    public function saveUnavailability(): void
    {
        $this->validate([
            'editingDate' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($this->editFullDay) {
            BookingUnavailability::replaceDayBlocks($this->editingDate, true, []);
        } elseif (count($this->editSlots) > 0) {
            BookingUnavailability::replaceDayBlocks($this->editingDate, false, $this->editSlots);
        } else {
            BookingUnavailability::clearDay($this->editingDate);
        }

        Notification::make()
            ->title('Availability updated')
            ->success()
            ->send();

        $this->closeEditor();
    }

    public function clearUnavailability(): void
    {
        if ($this->editingDate) {
            BookingUnavailability::clearDay($this->editingDate);
            Notification::make()
                ->title('All blocks removed for this day')
                ->success()
                ->send();
        }
        $this->closeEditor();
    }

    /** @return list<string> */
    public function getSlotLabels(): array
    {
        return BookingTimeSlots::labels();
    }
}
