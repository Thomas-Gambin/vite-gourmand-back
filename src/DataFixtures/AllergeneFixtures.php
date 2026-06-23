<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Allergene;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AllergeneFixtures extends Fixture
{
    public const ALLERGENE_GLUTEN = 'allergene_gluten';
    public const ALLERGENE_LAIT = 'allergene_lait';
    public const ALLERGENE_OEUFS = 'allergene_oeufs';
    public const ALLERGENE_ARACHIDES = 'allergene_arachides';
    public const ALLERGENE_FRUITS_A_COQUE = 'allergene_fruits_a_coque';
    public const ALLERGENE_SOJA = 'allergene_soja';
    public const ALLERGENE_POISSON = 'allergene_poisson';
    public const ALLERGENE_CRUSTACES = 'allergene_crustaces';
    public const ALLERGENE_MOLLUSQUES = 'allergene_mollusques';
    public const ALLERGENE_MOUTARDE = 'allergene_moutarde';
    public const ALLERGENE_CELERI = 'allergene_celeri';
    public const ALLERGENE_SESAME = 'allergene_sesame';
    public const ALLERGENE_SULFITES = 'allergene_sulfites';
    public const ALLERGENE_LUPIN = 'allergene_lupin';

    public function load(ObjectManager $manager): void
    {
        $allergenes = [
            self::ALLERGENE_GLUTEN => 'Gluten',
            self::ALLERGENE_LAIT => 'Lait',
            self::ALLERGENE_OEUFS => 'Œufs',
            self::ALLERGENE_ARACHIDES => 'Arachides',
            self::ALLERGENE_FRUITS_A_COQUE => 'Fruits à coque',
            self::ALLERGENE_SOJA => 'Soja',
            self::ALLERGENE_POISSON => 'Poisson',
            self::ALLERGENE_CRUSTACES => 'Crustacés',
            self::ALLERGENE_MOLLUSQUES => 'Mollusques',
            self::ALLERGENE_MOUTARDE => 'Moutarde',
            self::ALLERGENE_CELERI => 'Céleri',
            self::ALLERGENE_SESAME => 'Sésame',
            self::ALLERGENE_SULFITES => 'Sulfites',
            self::ALLERGENE_LUPIN => 'Lupin',
        ];

        foreach ($allergenes as $reference => $libelle) {
            $allergene = new Allergene();
            $allergene->setLibelle($libelle);
            $manager->persist($allergene);
            $this->addReference($reference, $allergene);
        }

        $manager->flush();
    }
}
