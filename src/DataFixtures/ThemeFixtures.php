<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public const THEME_NOEL = 'theme_noel';
    public const THEME_PAQUES = 'theme_paques';
    public const THEME_CLASSIQUE = 'theme_classique';
    public const THEME_EVENEMENT = 'theme_evenement';
    public const THEME_MARIAGE = 'theme_mariage';
    public const THEME_ANNIVERSAIRE = 'theme_anniversaire';
    public const THEME_ENTREPRISE = 'theme_entreprise';

    public function load(ObjectManager $manager): void
    {
        $themes = [
            self::THEME_NOEL => 'Noël',
            self::THEME_PAQUES => 'Pâques',
            self::THEME_CLASSIQUE => 'Classique',
            self::THEME_EVENEMENT => 'Événement',
            self::THEME_MARIAGE => 'Mariage',
            self::THEME_ANNIVERSAIRE => 'Anniversaire',
            self::THEME_ENTREPRISE => 'Entreprise',
        ];

        foreach ($themes as $reference => $libelle) {
            $theme = new Theme();
            $theme->setLibelle($libelle);
            $manager->persist($theme);
            $this->addReference($reference, $theme);
        }

        $manager->flush();
    }
}
