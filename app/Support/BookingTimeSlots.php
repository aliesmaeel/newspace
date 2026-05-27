<?php

namespace App\Support;

use Carbon\Carbon;

final class BookingTimeSlots
{
    public const START_MINUTES = 10 * 60;

    public const END_MINUTES = 17 * 60;

    /**
     * @return list<string> Labels like "10:00 AM", "10:30 AM", … "5:00 PM"
     */
    public static function labels(): array
    {
        $slots = [];
        for ($mins = self::START_MINUTES; $mins <= self::END_MINUTES; $mins += 30) {
            $h24 = intdiv($mins, 60);
            $m = $mins % 60;
            $isPm = $h24 >= 12;
            $h12 = $h24 % 12;
            if ($h12 === 0) {
                $h12 = 12;
            }
            $mm = $m === 0 ? '00' : '30';
            $slots[] = "{$h12}:{$mm} " . ($isPm ? 'PM' : 'AM');
        }

        return $slots;
    }

    public static function isWeekend(string $dateYmd): bool
    {
        $day = Carbon::createFromFormat('Y-m-d', $dateYmd)->dayOfWeek;

        return in_array($day, [Carbon::SATURDAY, Carbon::SUNDAY], true);
    }

    public static function isValidLabel(string $label): bool
    {
        return in_array($label, self::labels(), true);
    }

    public static function labelToTimeString(string $label): string
    {
        $dt = Carbon::createFromFormat('g:i A', trim($label));

        return $dt->format('H:i:s');
    }
}
