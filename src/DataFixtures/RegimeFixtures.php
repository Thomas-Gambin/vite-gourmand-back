<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Regime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RegimeFixtures extends Fixture
{
    public const REGIME_CLASSIQUE = 'regime_classique';
    public const REGIME_VEGETARIEN = 'regime_vegetarien';
    public const REGIME_VEGAN = 'regime_vegan';
    public const REGIME_SANS_GLUTEN = 'regime_sans_gluten';
    public const REGIME_SANS_LACTOSE = 'regime_sans_lactose';

    public function load(ObjectManager $manager): void
    {
        $regimes = [
            self::REGIME_CLASSIQUE => 'Classique',
            self::REGIME_VEGETARIEN => 'Végétarien',
            self::REGIME_VEGAN => 'Vegan',
            self::REGIME_SANS_GLUTEN => 'Sans gluten',
            self::REGIME_SANS_LACTOSE => 'Sans lactose',
        ];

        foreach ($regimes as $reference => $libelle) {
            $regime = new Regime();
            $regime->setLibelle($libelle);
            $manager->persist($regime);
            $this->addReference($reference, $regime);
        }

        $manager->flush();
    }
}
