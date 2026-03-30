<?php

namespace App\Support;

final class BookingTimeSlots
{
    /**
     * @return list<string> Labels like "9:00 AM", "9:30 AM", … "6:00 PM"
     */
    public static function labels(): array
    {
        $slots = [];
        for ($mins = 9 * 60; $mins <= 18 * 60; $mins += 30) {
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

    public static function isValidLabel(string $label): bool
    {
        return in_array($label, self::labels(), true);
    }

    /**
     * Convert "9:00 AM" to H:i:s for storage.
     */
    public static function labelToTimeString(string $label): string
    {
        $dt = \Carbon\Carbon::createFromFormat('g:i A', trim($label));

        return $dt->format('H:i:s');
    }
}
