<?php

declare(strict_types=1);

namespace App\Service;

final class AvisStatus
{
    public const EN_ATTENTE = 'en_attente';
    public const VALIDE = 'valide';
    public const REFUSE = 'refuse';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::EN_ATTENTE, self::VALIDE, self::REFUSE];
    }

    public static function isValid(string $statut): bool
    {
        return in_array($statut, self::all(), true);
    }

    public static function isModeratable(string $statut): bool
    {
        return $statut === self::EN_ATTENTE;
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::EN_ATTENTE => 'En attente',
            self::VALIDE => 'Validé',
            self::REFUSE => 'Refusé',
        ];
    }

    public static function label(string $statut): string
    {
        return self::labels()[$statut] ?? $statut;
    }

    public static function badgeClass(string $statut): string
    {
        return match ($statut) {
            self::EN_ATTENTE => 'warning',
            self::VALIDE => 'success',
            self::REFUSE => 'danger',
            default => 'secondary',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function statusChoices(): array
    {
        $choices = [];
        foreach (self::all() as $status) {
            $choices[self::label($status)] = $status;
        }

        return $choices;
    }
}
