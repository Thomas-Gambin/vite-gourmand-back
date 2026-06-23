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
}
