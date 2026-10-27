<?php

namespace App\Support;

final class Money
{
    public static function round(float|int|string $amount): float
    {
        return round((float) $amount, 2);
    }

    public static function format(float|int|string|null $amount): string
    {
        return '₹'.number_format(self::round($amount ?? 0), 2);
    }
}
