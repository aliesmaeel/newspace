<?php

namespace App\Support;

class Money
{
    public static function currency(): string
    {
        return strtolower((string) config('services.stripe.currency', 'gbp'));
    }

    public static function symbol(): string
    {
        return match (self::currency()) {
            'gbp' => '£',
            'usd' => '$',
            'eur' => '€',
            default => strtoupper(self::currency()) . ' ',
        };
    }

    public static function formatCents(int $cents): string
    {
        return self::symbol() . number_format($cents / 100, 2);
    }
}
