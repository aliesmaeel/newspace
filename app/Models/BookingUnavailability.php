<?php

namespace App\Models;

use App\Support\BookingTimeSlots;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BookingUnavailability extends Model
{
    protected $fillable = [
        'blocked_date',
        'is_full_day',
        'slot_time',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
            'is_full_day' => 'boolean',
        ];
    }

    /**
     * @return list<string> Slot labels blocked on this calendar date (Y-m-d).
     */
    public static function blockedSlotLabelsForDate(string $dateYmd): array
    {
        $all = BookingTimeSlots::labels();

        if (static::query()->whereDate('blocked_date', $dateYmd)->where('is_full_day', true)->exists()) {
            return $all;
        }

        return static::query()
            ->whereDate('blocked_date', $dateYmd)
            ->where('is_full_day', false)
            ->whereNotNull('slot_time')
            ->pluck('slot_time')
            ->map(fn ($t) => Carbon::parse($t)->format('g:i A'))
            ->unique()
            ->values()
            ->all();
    }

    public static function replaceDayBlocks(string $dateYmd, bool $fullDay, array $slotLabels): void
    {
        static::query()->whereDate('blocked_date', $dateYmd)->delete();

        if ($fullDay) {
            static::query()->create([
                'blocked_date' => $dateYmd,
                'is_full_day' => true,
                'slot_time' => null,
            ]);

            return;
        }

        foreach ($slotLabels as $label) {
            if (! BookingTimeSlots::isValidLabel($label)) {
                continue;
            }
            static::query()->create([
                'blocked_date' => $dateYmd,
                'is_full_day' => false,
                'slot_time' => BookingTimeSlots::labelToTimeString($label),
            ]);
        }
    }

    public static function clearDay(string $dateYmd): void
    {
        static::query()->whereDate('blocked_date', $dateYmd)->delete();
    }
}
