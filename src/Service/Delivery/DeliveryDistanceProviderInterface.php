<?php

declare(strict_types=1);

namespace App\Service\Delivery;

interface DeliveryDistanceProviderInterface
{
    public function resolveDistanceKm(string $adresse, string $ville, ?string $codePostal): float;
}
