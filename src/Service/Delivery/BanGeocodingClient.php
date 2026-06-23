<?php

declare(strict_types=1);

namespace App\Service\Delivery;

use App\Exception\GeocodingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BanGeocodingClient
{
    private const string BASE_URL = 'https://api-adresse.data.gouv.fr/search/';
    private const float MIN_SCORE = 0.5;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array{lat: float, lon: float}
     */
    public function geocode(string $adresse, string $ville, ?string $codePostal): array
    {
        $query = trim(sprintf(
            '%s %s %s',
            trim($adresse),
            trim((string) $codePostal),
            trim($ville),
        ));

        if ($query === '') {
            throw new GeocodingException('Adresse de prestation vide.');
        }

        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'q' => $query,
                'limit' => 1,
            ],
        ]);

        /** @var array{features?: list<array{geometry?: array{coordinates?: list<float|int>}, properties?: array{score?: float|int}}>} $data */
        $data = $response->toArray(false);
        $feature = $data['features'][0] ?? null;

        if ($feature === null) {
            throw new GeocodingException('Aucun résultat de géocodage.');
        }

        $score = (float) ($feature['properties']['score'] ?? 0);
        if ($score < self::MIN_SCORE) {
            throw new GeocodingException('Résultat de géocodage trop imprécis.');
        }

        $coordinates = $feature['geometry']['coordinates'] ?? null;
        if (!is_array($coordinates) || count($coordinates) < 2) {
            throw new GeocodingException('Coordonnées de géocodage invalides.');
        }

        return [
            'lat' => (float) $coordinates[1],
            'lon' => (float) $coordinates[0],
        ];
    }
}
