<?php

declare(strict_types=1);

namespace App\Dto\Horaire;

use App\Entity\Horaire;

final readonly class HoraireDto
{
    public function __construct(
        public string $day,
        public string $hours,
        public bool $isClosed,
    ) {
    }

    public static function fromEntity(Horaire $horaire): self
    {
        $opening = (string) $horaire->getHeureOuverture();
        $closing = (string) $horaire->getHeureFermeture();
        $isClosed = self::isClosedValue($opening) || self::isClosedValue($closing);

        return new self(
            day: (string) $horaire->getJour(),
            hours: $isClosed ? 'Fermé' : sprintf('%s - %s', self::formatHour($opening), self::formatHour($closing)),
            isClosed: $isClosed,
        );
    }

    /**
     * @return array{day: string, hours: string, isClosed: bool}
     */
    public function toArray(): array
    {
        return [
            'day' => $this->day,
            'hours' => $this->hours,
            'isClosed' => $this->isClosed,
        ];
    }

    private static function isClosedValue(string $value): bool
    {
        return strcasecmp(trim($value), 'Fermé') === 0;
    }

    private static function formatHour(string $value): string
    {
        if (self::isClosedValue($value)) {
            return 'Fermé';
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($value), $matches) === 1) {
            return sprintf('%02dh%s', (int) $matches[1], $matches[2]);
        }

        return $value;
    }
}
