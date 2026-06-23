<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Calculs monétaires en centimes (entiers) pour éviter les erreurs de float.
 */
final class MoneyMath
{
    public static function parseToCents(string $amount): int
    {
        $normalized = number_format((float) $amount, 2, '.', '');
        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '00');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return (int) $whole * 100 + (int) $fraction;
    }

    public static function format(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        $formatted = sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);

        return $negative ? '-'.$formatted : $formatted;
    }

    public static function multiply(string $amount, int $factor): string
    {
        return self::format(self::parseToCents($amount) * $factor);
    }

    public static function percentage(string $amount, int $percent): string
    {
        $cents = self::parseToCents($amount);

        return self::format(intdiv($cents * $percent, 100));
    }

    public static function subtract(string $left, string $right): string
    {
        return self::format(self::parseToCents($left) - self::parseToCents($right));
    }

    public static function add(string $left, string $right): string
    {
        return self::format(self::parseToCents($left) + self::parseToCents($right));
    }
}
