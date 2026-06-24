<?php

declare(strict_types=1);

namespace App\Service;

final class CommandeStatus
{
    public const EN_ATTENTE = 'en_attente';
    public const ACCEPTE = 'accepte';
    public const EN_PREPARATION = 'en_preparation';
    public const EN_COURS_DE_LIVRAISON = 'en_cours_de_livraison';
    public const LIVRE = 'livre';
    public const EN_ATTENTE_RETOUR_MATERIEL = 'en_attente_retour_materiel';
    public const TERMINEE = 'terminee';
    public const ANNULEE = 'annulee';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::EN_ATTENTE,
            self::ACCEPTE,
            self::EN_PREPARATION,
            self::EN_COURS_DE_LIVRAISON,
            self::LIVRE,
            self::EN_ATTENTE_RETOUR_MATERIEL,
            self::TERMINEE,
            self::ANNULEE,
        ];
    }

    public static function isValid(string $statut): bool
    {
        return in_array($statut, self::all(), true);
    }

    public static function isEditable(string $statut): bool
    {
        return $statut === self::EN_ATTENTE;
    }

    public static function isCancellable(string $statut): bool
    {
        return $statut === self::EN_ATTENTE;
    }

    public static function isTrackable(string $statut): bool
    {
        return in_array($statut, [
            self::ACCEPTE,
            self::EN_PREPARATION,
            self::EN_COURS_DE_LIVRAISON,
            self::LIVRE,
            self::EN_ATTENTE_RETOUR_MATERIEL,
            self::TERMINEE,
            self::ANNULEE,
        ], true);
    }

    public static function isReviewable(string $statut): bool
    {
        return $statut === self::TERMINEE;
    }

    /**
     * @return list<string>
     */
    public static function employeeUpdatableStatuses(): array
    {
        return [
            self::ACCEPTE,
            self::EN_PREPARATION,
            self::EN_COURS_DE_LIVRAISON,
            self::LIVRE,
            self::EN_ATTENTE_RETOUR_MATERIEL,
            self::TERMINEE,
            self::ANNULEE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::EN_ATTENTE => 'En attente',
            self::ACCEPTE => 'Acceptée',
            self::EN_PREPARATION => 'En préparation',
            self::EN_COURS_DE_LIVRAISON => 'En cours de livraison',
            self::LIVRE => 'Livrée',
            self::EN_ATTENTE_RETOUR_MATERIEL => 'En attente du retour de matériel',
            self::TERMINEE => 'Terminée',
            self::ANNULEE => 'Annulée',
        ];
    }

    public static function label(string $statut): string
    {
        return self::labels()[$statut] ?? $statut;
    }

    public static function badgeClass(string $statut): string
    {
        return match ($statut) {
            self::EN_ATTENTE => 'secondary',
            self::ACCEPTE => 'info',
            self::EN_PREPARATION => 'primary',
            self::EN_COURS_DE_LIVRAISON => 'warning',
            self::LIVRE => 'success',
            self::EN_ATTENTE_RETOUR_MATERIEL => 'warning',
            self::TERMINEE => 'success',
            self::ANNULEE => 'danger',
            default => 'secondary',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function employeeUpdatableStatusChoices(): array
    {
        $choices = [];
        foreach (self::employeeUpdatableStatuses() as $status) {
            $choices[self::label($status)] = $status;
        }

        return $choices;
    }

    /**
     * Ordre chronologique normal d'avancement d'une commande (hors annulation).
     *
     * @return list<string>
     */
    public static function pipeline(): array
    {
        return [
            self::EN_ATTENTE,
            self::ACCEPTE,
            self::EN_PREPARATION,
            self::EN_COURS_DE_LIVRAISON,
            self::LIVRE,
            self::EN_ATTENTE_RETOUR_MATERIEL,
            self::TERMINEE,
        ];
    }

    /**
     * Statuts à enregistrer dans l'historique lors d'une transition.
     * Chaque nouveau statut est une entrée distincte ; les précédents sont conservés.
     *
     * @return list<string>
     */
    public static function getStatutsToRecordOnTransition(string $from, string $to): array
    {
        if ($to === self::ANNULEE) {
            return [$to];
        }

        $pipeline = self::pipeline();
        $fromIdx = array_search($from, $pipeline, true);
        $toIdx = array_search($to, $pipeline, true);

        if ($fromIdx === false || $toIdx === false) {
            return [$to];
        }

        if ($toIdx > $fromIdx) {
            return array_slice($pipeline, $fromIdx + 1, $toIdx - $fromIdx);
        }

        return [$to];
    }
}
