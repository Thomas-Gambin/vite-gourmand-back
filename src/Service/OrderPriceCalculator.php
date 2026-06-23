<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Commande\OrderPriceBreakdown;
use App\Entity\Menu;

final class OrderPriceCalculator
{
    public function calculate(Menu $menu, int $nombrePersonne, string $villePrestation): OrderPriceBreakdown
    {
        $prixParPersonne = MoneyMath::format(MoneyMath::parseToCents((string) $menu->getPrixParPersonne()));
        $sousTotal = MoneyMath::multiply($prixParPersonne, $nombrePersonne);

        $reductionApplied = $nombrePersonne >= $menu->getNombrePersonneMinimum() + 5;
        $remise = '0.00';
        if ($reductionApplied) {
            $remise = MoneyMath::percentage($sousTotal, 10);
        }

        $prixMenu = MoneyMath::subtract($sousTotal, $remise);
        $prixLivraison = $this->calculateDeliveryPrice($villePrestation);
        $total = MoneyMath::add($prixMenu, $prixLivraison);

        return new OrderPriceBreakdown(
            prixParPersonne: $prixParPersonne,
            nombrePersonne: $nombrePersonne,
            sousTotal: $sousTotal,
            remise: $remise,
            prixMenu: $prixMenu,
            prixLivraison: $prixLivraison,
            total: $total,
            reductionApplied: $reductionApplied,
        );
    }

    public function calculateDeliveryPrice(string $city): string
    {
        if (mb_strtolower(trim($city)) === 'bordeaux') {
            return '0.00';
        }

        // TODO: intégrer un service de distance réel (5 € + 0,59 €/km).
        return '5.00';
    }
}
