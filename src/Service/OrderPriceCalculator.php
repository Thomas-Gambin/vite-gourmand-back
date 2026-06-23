<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Commande\OrderPriceBreakdown;
use App\Entity\Menu;
use App\Service\Delivery\DeliveryCityNormalizer;
use App\Service\Delivery\DeliveryDistanceProviderInterface;

final class OrderPriceCalculator
{
    private const string DELIVERY_BASE_PRICE = '5.00';
    private const string DELIVERY_RATE_PER_KM = '0.59';

    public function __construct(
        private readonly DeliveryDistanceProviderInterface $distanceProvider,
    ) {
    }

    public function calculate(
        Menu $menu,
        int $nombrePersonne,
        string $adressePrestation,
        string $villePrestation,
        ?string $codePostalPrestation,
    ): OrderPriceBreakdown {
        $prixParPersonne = MoneyMath::format(MoneyMath::parseToCents((string) $menu->getPrixParPersonne()));
        $sousTotal = MoneyMath::multiply($prixParPersonne, $nombrePersonne);

        $reductionApplied = $nombrePersonne >= $menu->getNombrePersonneMinimum() + 5;
        $remise = '0.00';
        if ($reductionApplied) {
            $remise = MoneyMath::percentage($sousTotal, 10);
        }

        $prixMenu = MoneyMath::subtract($sousTotal, $remise);
        [$prixLivraison, $distanceLivraisonKm] = $this->resolveDeliveryPrice(
            $adressePrestation,
            $villePrestation,
            $codePostalPrestation,
        );
        $total = MoneyMath::add($prixMenu, $prixLivraison);

        return new OrderPriceBreakdown(
            prixParPersonne: $prixParPersonne,
            nombrePersonne: $nombrePersonne,
            nombrePersonneMinimum: $menu->getNombrePersonneMinimum(),
            sousTotal: $sousTotal,
            remise: $remise,
            prixMenu: $prixMenu,
            prixLivraison: $prixLivraison,
            total: $total,
            reductionApplied: $reductionApplied,
            villePrestation: trim($villePrestation),
            distanceLivraisonKm: $distanceLivraisonKm,
        );
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function resolveDeliveryPrice(
        string $adressePrestation,
        string $villePrestation,
        ?string $codePostalPrestation,
    ): array {
        if (DeliveryCityNormalizer::isBordeaux($villePrestation)) {
            return ['0.00', null];
        }

        $distanceKm = $this->distanceProvider->resolveDistanceKm(
            $adressePrestation,
            $villePrestation,
            $codePostalPrestation,
        );

        $variablePart = MoneyMath::multiplyRate(self::DELIVERY_RATE_PER_KM, $distanceKm);
        $prixLivraison = MoneyMath::add(self::DELIVERY_BASE_PRICE, $variablePart);

        return [$prixLivraison, number_format($distanceKm, 2, '.', '')];
    }
}
