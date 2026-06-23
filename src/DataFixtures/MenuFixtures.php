<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_NOEL = 'menu_noel';
    public const MENU_PAQUES = 'menu_paques';
    public const MENU_CLASSIQUE_BORDELAIS = 'menu_classique_bordelais';
    public const MENU_VEGETARIEN = 'menu_vegetarien';
    public const MENU_VEGAN = 'menu_vegan';
    public const MENU_MARIAGE = 'menu_mariage';
    public const MENU_ENTREPRISE = 'menu_entreprise';
    public const MENU_ANNIVERSAIRE = 'menu_anniversaire';

    public function load(ObjectManager $manager): void
    {
        $menus = [
            [
                'reference' => self::MENU_NOEL,
                'titre' => 'Menu Tradition de Noël',
                'description' => 'Un menu festif et généreux pour célébrer Noël en famille ou entre amis.',
                'theme' => ThemeFixtures::THEME_NOEL,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 6,
                'prixParPersonne' => '42.00',
                'quantiteRestante' => 8,
                'plats' => [PlatFixtures::PLAT_FOIE_GRAS, PlatFixtures::PLAT_SUPREME_VOLAILLE, PlatFixtures::PLAT_BUCHE_CHOCOLAT],
            ],
            [
                'reference' => self::MENU_PAQUES,
                'titre' => 'Menu Pâques Gourmand',
                'description' => 'Un menu printanier autour de produits frais et d\'un plat familial traditionnel.',
                'theme' => ThemeFixtures::THEME_PAQUES,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 4,
                'prixParPersonne' => '36.00',
                'quantiteRestante' => 10,
                'plats' => [PlatFixtures::PLAT_SALADE_ASPERGES, PlatFixtures::PLAT_GIGOT_AGNEAU, PlatFixtures::PLAT_TARTE_CITRON],
            ],
            [
                'reference' => self::MENU_CLASSIQUE_BORDELAIS,
                'titre' => 'Menu Classique Bordelais',
                'description' => 'Une formule complète et élégante, idéale pour un repas convivial.',
                'theme' => ThemeFixtures::THEME_CLASSIQUE,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 2,
                'prixParPersonne' => '29.00',
                'quantiteRestante' => 15,
                'plats' => [PlatFixtures::PLAT_FEUILLETE_CHEVRE, PlatFixtures::PLAT_FILET_BOEUF, PlatFixtures::PLAT_ENTREMETS_FRAISE],
            ],
            [
                'reference' => self::MENU_VEGETARIEN,
                'titre' => 'Menu Végétarien de Saison',
                'description' => 'Un menu végétarien équilibré, gourmand et préparé avec des produits de saison.',
                'theme' => ThemeFixtures::THEME_CLASSIQUE,
                'regime' => RegimeFixtures::REGIME_VEGETARIEN,
                'nombrePersonneMinimum' => 3,
                'prixParPersonne' => '27.00',
                'quantiteRestante' => 12,
                'plats' => [PlatFixtures::PLAT_VELOUTE_POTIMARRON, PlatFixtures::PLAT_RISOTTO_CHAMPIGNONS, PlatFixtures::PLAT_PAVLOVA],
            ],
            [
                'reference' => self::MENU_VEGAN,
                'titre' => 'Menu Vegan Festif',
                'description' => 'Une proposition 100% végétale, pensée pour les événements et repas conviviaux.',
                'theme' => ThemeFixtures::THEME_EVENEMENT,
                'regime' => RegimeFixtures::REGIME_VEGAN,
                'nombrePersonneMinimum' => 4,
                'prixParPersonne' => '31.00',
                'quantiteRestante' => 9,
                'plats' => [PlatFixtures::PLAT_SALADE_ASPERGES, PlatFixtures::PLAT_CURRY_VEGAN, PlatFixtures::PLAT_MOELLEUX_VEGAN],
            ],
            [
                'reference' => self::MENU_MARIAGE,
                'titre' => 'Menu Mariage Élégance',
                'description' => 'Un menu raffiné conçu pour accompagner les grandes réceptions.',
                'theme' => ThemeFixtures::THEME_MARIAGE,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 20,
                'prixParPersonne' => '58.00',
                'quantiteRestante' => 4,
                'plats' => [PlatFixtures::PLAT_TARTARE_SAUMON, PlatFixtures::PLAT_FILET_BOEUF, PlatFixtures::PLAT_ENTREMETS_FRAISE],
            ],
            [
                'reference' => self::MENU_ENTREPRISE,
                'titre' => 'Buffet Entreprise',
                'description' => 'Une formule pratique et complète pour les repas professionnels, réunions et séminaires.',
                'theme' => ThemeFixtures::THEME_ENTREPRISE,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 10,
                'prixParPersonne' => '24.00',
                'quantiteRestante' => 20,
                'plats' => [PlatFixtures::PLAT_FEUILLETE_CHEVRE, PlatFixtures::PLAT_PAVE_SAUMON, PlatFixtures::PLAT_TARTE_CITRON],
            ],
            [
                'reference' => self::MENU_ANNIVERSAIRE,
                'titre' => 'Menu Anniversaire Gourmand',
                'description' => 'Une formule généreuse et festive pour partager un moment chaleureux.',
                'theme' => ThemeFixtures::THEME_ANNIVERSAIRE,
                'regime' => RegimeFixtures::REGIME_CLASSIQUE,
                'nombrePersonneMinimum' => 6,
                'prixParPersonne' => '34.00',
                'quantiteRestante' => 11,
                'plats' => [PlatFixtures::PLAT_VELOUTE_POTIMARRON, PlatFixtures::PLAT_SUPREME_VOLAILLE, PlatFixtures::PLAT_PAVLOVA],
            ],
        ];

        foreach ($menus as $data) {
            $menu = new Menu();
            $menu->setTitre($data['titre']);
            $menu->setDescription($data['description']);
            $menu->setNombrePersonneMinimum($data['nombrePersonneMinimum']);
            $menu->setPrixParPersonne($data['prixParPersonne']);
            $menu->setQuantiteRestante($data['quantiteRestante']);
            $menu->setTheme($this->getReference($data['theme'], Theme::class));
            $menu->setRegime($this->getReference($data['regime'], Regime::class));

            foreach ($data['plats'] as $platRef) {
                $menu->addPlat($this->getReference($platRef, Plat::class));
            }

            $manager->persist($menu);
            $this->addReference($data['reference'], $menu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ThemeFixtures::class,
            RegimeFixtures::class,
            PlatFixtures::class,
        ];
    }
}
