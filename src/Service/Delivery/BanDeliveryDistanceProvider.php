<?php

declare(strict_types=1);

namespace App\Service\Delivery;

final class BanDeliveryDistanceProvider implements DeliveryDistanceProviderInterface
{
    public function __construct(
        private readonly BanGeocodingClient $geocodingClient,
        private readonly HaversineDistanceCalculator $distanceCalculator,
        private readonly float $originLat,
        private readonly float $originLon,
    ) {
    }

    public function resolveDistanceKm(string $adresse, string $ville, ?string $codePostal): float
    {
        $coordinates = $this->geocodingClient->geocode($adresse, $ville, $codePostal);

        return $this->distanceCalculator->distanceKm(
            $this->originLat,
            $this->originLon,
            $coordinates['lat'],
            $coordinates['lon'],
        );
    }
}
