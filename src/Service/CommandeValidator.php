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
                'Le nombre de personnes doit être au minimum de %d pour ce menu.',
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
    public function validateForPreview(?Menu $menu, int $nombrePersonne): array
    {
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
                'Le nombre de personnes doit être au minimum de %d pour ce menu.',
                $menu->getNombrePersonneMinimum()
            );
        }

        return $fields;
    }
}
