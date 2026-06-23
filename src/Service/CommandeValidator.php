<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Menu;

final class CommandeValidator
{
    /**
     * @return array<string, string>
     */
    public function validateForOrder(
        ?Menu $menu,
        int $nombrePersonne,
        string $datePrestation,
        string $heureLivraison,
    ): array {
        $fields = [];

        if ($menu === null) {
            return ['menuId' => 'Le menu sélectionné est introuvable.'];
        }

        if ($menu->getQuantiteRestante() <= 0) {
            $fields['menuId'] = 'Ce menu n’est plus disponible (stock épuisé).';
        }

        if ($nombrePersonne <= 0) {
            $fields['nombrePersonne'] = 'Le nombre de personnes doit être strictement positif.';
        } elseif ($nombrePersonne < $menu->getNombrePersonneMinimum()) {
            $fields['nombrePersonne'] = sprintf(
                'Ce menu est disponible à partir de %d personnes minimum.',
                $menu->getNombrePersonneMinimum()
            );
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $datePrestation);
        if ($date === false) {
            $fields['datePrestation'] = 'La date de prestation est invalide.';
        } elseif ($date < new \DateTimeImmutable('today')) {
            $fields['datePrestation'] = 'La date de prestation ne peut pas être dans le passé.';
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $heureLivraison)) {
            $fields['heureLivraison'] = 'L’heure de livraison doit être au format HH:MM.';
        } else {
            $time = \DateTimeImmutable::createFromFormat('H:i', $heureLivraison);
            if ($time === false || $time->format('H:i') !== $heureLivraison) {
                $fields['heureLivraison'] = 'L’heure de livraison est invalide.';
            }
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    public function validateForPreview(
        ?Menu $menu,
        int $nombrePersonne,
        string $adressePrestation,
        string $villePrestation,
    ): array {
        $fields = [];

        if ($menu === null) {
            return ['menuId' => 'Le menu sélectionné est introuvable.'];
        }

        if ($menu->getQuantiteRestante() <= 0) {
            $fields['menuId'] = 'Ce menu n’est plus disponible (stock épuisé).';
        }

        if (trim($adressePrestation) === '') {
            $fields['adressePrestation'] = "L'adresse de prestation est obligatoire.";
        }

        if (trim($villePrestation) === '') {
            $fields['villePrestation'] = 'La ville de prestation est obligatoire.';
        }

        if ($nombrePersonne <= 0) {
            $fields['nombrePersonne'] = 'Le nombre de personnes doit être strictement positif.';
        } elseif ($nombrePersonne < $menu->getNombrePersonneMinimum()) {
            $fields['nombrePersonne'] = sprintf(
                'Ce menu est disponible à partir de %d personnes minimum.',
                $menu->getNombrePersonneMinimum()
            );
        }

        return $fields;
    }
}
