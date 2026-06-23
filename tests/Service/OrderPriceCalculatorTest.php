<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Menu;
use App\Service\OrderPriceCalculator;
use PHPUnit\Framework\TestCase;

final class OrderPriceCalculatorTest extends TestCase
{
    private OrderPriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new OrderPriceCalculator();
    }

    public function testAppliesTenPercentDiscountWhenThresholdReached(): void
    {
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $this->calculator->calculate($menu, 11, 'Bordeaux');

        self::assertTrue($breakdown->reductionApplied);
        self::assertSame('462.00', $breakdown->sousTotal);
        self::assertSame('46.20', $breakdown->remise);
        self::assertSame('415.80', $breakdown->prixMenu);
        self::assertSame('0.00', $breakdown->prixLivraison);
        self::assertSame('415.80', $breakdown->total);
    }

    public function testNoDiscountBelowThreshold(): void
    {
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $this->calculator->calculate($menu, 8, 'Bordeaux');

        self::assertFalse($breakdown->reductionApplied);
        self::assertSame('0.00', $breakdown->remise);
        self::assertSame('336.00', $breakdown->prixMenu);
    }

    public function testDeliveryIsFreeInBordeaux(): void
    {
        self::assertSame('0.00', $this->calculator->calculateDeliveryPrice('Bordeaux'));
        self::assertSame('0.00', $this->calculator->calculateDeliveryPrice(' bordeaux '));
    }

    public function testDeliveryOutsideBordeauxIsFlatRate(): void
    {
        self::assertSame('5.00', $this->calculator->calculateDeliveryPrice('Paris'));

        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);
        $breakdown = $this->calculator->calculate($menu, 8, 'Paris');

        self::assertSame('5.00', $breakdown->prixLivraison);
        self::assertSame('341.00', $breakdown->total);
    }

    public function testExampleWithDiscountAtThreshold(): void
    {
        $menu = $this->createMenu(prixParPersonne: '42.00', nombrePersonneMinimum: 6);

        $breakdown = $this->calculator->calculate($menu, 11, 'Bordeaux');

        self::assertSame('42.00', $breakdown->prixParPersonne);
        self::assertSame(11, $breakdown->nombrePersonne);
        self::assertSame('462.00', $breakdown->sousTotal);
        self::assertSame('46.20', $breakdown->remise);
        self::assertSame('415.80', $breakdown->prixMenu);
        self::assertSame('0.00', $breakdown->prixLivraison);
        self::assertSame('415.80', $breakdown->total);
        self::assertTrue($breakdown->reductionApplied);
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
