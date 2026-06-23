<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Menu;
use App\Service\Delivery\DeliveryDistanceProviderInterface;
use App\Service\OrderPriceCalculator;
use PHPUnit\Framework\TestCase;

final class OrderPriceCalculatorTest extends TestCase
{
    public function testAppliesTenPercentDiscountWhenThresholdReached(): void
    {
        $calculator = $this->createCalculator(20.0);
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $calculator->calculate($menu, 11, '1 rue Test', 'Bordeaux', '33000');

        self::assertTrue($breakdown->reductionApplied);
        self::assertSame('462.00', $breakdown->sousTotal);
        self::assertSame('46.20', $breakdown->remise);
        self::assertSame('415.80', $breakdown->prixMenu);
        self::assertSame('0.00', $breakdown->prixLivraison);
        self::assertNull($breakdown->distanceLivraisonKm);
        self::assertSame('415.80', $breakdown->total);
    }

    public function testNoDiscountBelowThreshold(): void
    {
        $calculator = $this->createCalculator(20.0);
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $calculator->calculate($menu, 8, '1 rue Test', 'Bordeaux', '33000');

        self::assertFalse($breakdown->reductionApplied);
        self::assertSame('0.00', $breakdown->remise);
        self::assertSame('336.00', $breakdown->prixMenu);
    }

    public function testDeliveryIsFreeInBordeaux(): void
    {
        $calculator = $this->createCalculator(20.0);
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $calculator->calculate($menu, 8, '1 rue Test', ' bordeaux ', '33000');

        self::assertSame('0.00', $breakdown->prixLivraison);
        self::assertNull($breakdown->distanceLivraisonKm);
    }

    public function testDeliveryOutsideBordeauxUsesDistanceFormula(): void
    {
        $calculator = $this->createCalculator(20.0);
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $calculator->calculate($menu, 8, '589 Allée des Cigalons', 'Vidauban', '83550');

        self::assertSame('20.00', $breakdown->distanceLivraisonKm);
        self::assertSame('16.80', $breakdown->prixLivraison);
        self::assertSame('352.80', $breakdown->total);
    }

    public function testExampleWithDiscountAtThreshold(): void
    {
        $calculator = $this->createCalculator(20.0);
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $calculator->calculate($menu, 11, '1 rue Test', 'Bordeaux', '33000');

        self::assertSame('42.00', $breakdown->prixParPersonne);
        self::assertSame(11, $breakdown->nombrePersonne);
        self::assertSame('462.00', $breakdown->sousTotal);
        self::assertSame('46.20', $breakdown->remise);
        self::assertSame('415.80', $breakdown->prixMenu);
        self::assertSame('0.00', $breakdown->prixLivraison);
        self::assertSame('415.80', $breakdown->total);
        self::assertTrue($breakdown->reductionApplied);
    }

    private function createCalculator(float $distanceKm): OrderPriceCalculator
    {
        $provider = new class($distanceKm) implements DeliveryDistanceProviderInterface {
            public function __construct(private readonly float $distanceKm)
            {
            }

            public function resolveDistanceKm(string $adresse, string $ville, ?string $codePostal): float
            {
                return $this->distanceKm;
            }
        };

        return new OrderPriceCalculator($provider);
    }

    private function createMenu(string $prixParPersonne, int $nombrePersonneMinimum): Menu
    {
        $menu = new Menu();
        $menu->setPrixParPersonne($prixParPersonne);
        $menu->setNombrePersonneMinimum($nombrePersonneMinimum);
        $menu->setTitre('Menu test');
        $menu->setDescription('Description test');
        $menu->setQuantiteRestante(10);

        return $menu;
    }
}
