<?php

declare(strict_types=1);

namespace App\Service\Delivery;

final class DeliveryCityNormalizer
{
    public static function normalize(string $city): string
    {
        return mb_strtolower(trim($city));
    }

    public static function isBordeaux(string $city): bool
    {
        return self::normalize($city) === 'bordeaux';
    }
}
