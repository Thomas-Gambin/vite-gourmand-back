<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Allergene;
use App\Entity\Plat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlatFixtures extends Fixture implements DependentFixtureInterface
{
    public const PLAT_FOIE_GRAS = 'plat_foie_gras';
    public const PLAT_VELOUTE_POTIMARRON = 'plat_veloute_potimarron';
    public const PLAT_SALADE_ASPERGES = 'plat_salade_asperges';
    public const PLAT_TARTARE_SAUMON = 'plat_tartare_saumon';
    public const PLAT_FEUILLETE_CHEVRE = 'plat_feuillete_chevre';
    public const PLAT_SUPREME_VOLAILLE = 'plat_supreme_volaille';
    public const PLAT_FILET_BOEUF = 'plat_filet_boeuf';
    public const PLAT_PAVE_SAUMON = 'plat_pave_saumon';
    public const PLAT_RISOTTO_CHAMPIGNONS = 'plat_risotto_champignons';
    public const PLAT_CURRY_VEGAN = 'plat_curry_vegan';
    public const PLAT_GIGOT_AGNEAU = 'plat_gigot_agneau';
    public const PLAT_BUCHE_CHOCOLAT = 'plat_buche_chocolat';
    public const PLAT_TARTE_CITRON = 'plat_tarte_citron';
    public const PLAT_ENTREMETS_FRAISE = 'plat_entremets_fraise';
    public const PLAT_MOELLEUX_VEGAN = 'plat_moelleux_vegan';
    public const PLAT_PAVLOVA = 'plat_pavlova';

    public function load(ObjectManager $manager): void
    {
        $plats = [
            [
                'reference' => self::PLAT_FOIE_GRAS,
                'titre' => 'Foie gras maison et chutney de figues',
                'photo' => '/uploads/plats/foie-gras-figues.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_SULFITES, AllergeneFixtures::ALLERGENE_GLUTEN],
            ],
            [
                'reference' => self::PLAT_VELOUTE_POTIMARRON,
                'titre' => 'Velouté de potimarron aux éclats de noisettes',
                'photo' => '/uploads/plats/veloute-potimarron.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_FRUITS_A_COQUE, AllergeneFixtures::ALLERGENE_LAIT],
            ],
            [
                'reference' => self::PLAT_SALADE_ASPERGES,
                'titre' => 'Salade printanière aux asperges',
                'photo' => '/uploads/plats/salade-asperges.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_MOUTARDE],
            ],
            [
                'reference' => self::PLAT_TARTARE_SAUMON,
                'titre' => 'Tartare de saumon aux agrumes',
                'photo' => '/uploads/plats/tartare-saumon.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_POISSON, AllergeneFixtures::ALLERGENE_SESAME],
            ],
            [
                'reference' => self::PLAT_FEUILLETE_CHEVRE,
                'titre' => 'Feuilleté de chèvre au miel',
                'photo' => '/uploads/plats/feuillete-chevre.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_GLUTEN, AllergeneFixtures::ALLERGENE_LAIT, AllergeneFixtures::ALLERGENE_OEUFS],
            ],
            [
                'reference' => self::PLAT_SUPREME_VOLAILLE,
                'titre' => 'Suprême de volaille sauce morilles',
                'photo' => '/uploads/plats/supreme-volaille.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_LAIT, AllergeneFixtures::ALLERGENE_SULFITES],
            ],
            [
                'reference' => self::PLAT_FILET_BOEUF,
                'titre' => 'Filet de bœuf rôti et gratin dauphinois',
                'photo' => '/uploads/plats/filet-boeuf.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_LAIT],
            ],
            [
                'reference' => self::PLAT_PAVE_SAUMON,
                'titre' => 'Pavé de saumon rôti aux herbes',
                'photo' => '/uploads/plats/pave-saumon.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_POISSON],
            ],
            [
                'reference' => self::PLAT_RISOTTO_CHAMPIGNONS,
                'titre' => 'Risotto crémeux aux champignons',
                'photo' => '/uploads/plats/risotto-champignons.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_LAIT, AllergeneFixtures::ALLERGENE_CELERI],
            ],
            [
                'reference' => self::PLAT_CURRY_VEGAN,
                'titre' => 'Curry vegan de légumes de saison',
                'photo' => '/uploads/plats/curry-vegan.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_SOJA],
            ],
            [
                'reference' => self::PLAT_GIGOT_AGNEAU,
                'titre' => 'Gigot d\'agneau confit aux herbes',
                'photo' => '/uploads/plats/gigot-agneau.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_SULFITES],
            ],
            [
                'reference' => self::PLAT_BUCHE_CHOCOLAT,
                'titre' => 'Bûche chocolat noisette',
                'photo' => '/uploads/plats/buche-chocolat.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_LAIT, AllergeneFixtures::ALLERGENE_OEUFS, AllergeneFixtures::ALLERGENE_FRUITS_A_COQUE, AllergeneFixtures::ALLERGENE_GLUTEN],
            ],
            [
                'reference' => self::PLAT_TARTE_CITRON,
                'titre' => 'Tarte citron meringuée',
                'photo' => '/uploads/plats/tarte-citron.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_GLUTEN, AllergeneFixtures::ALLERGENE_OEUFS, AllergeneFixtures::ALLERGENE_LAIT],
            ],
            [
                'reference' => self::PLAT_ENTREMETS_FRAISE,
                'titre' => 'Entremets fraise vanille',
                'photo' => '/uploads/plats/entremets-fraise.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_LAIT, AllergeneFixtures::ALLERGENE_OEUFS],
            ],
            [
                'reference' => self::PLAT_MOELLEUX_VEGAN,
                'titre' => 'Moelleux chocolat vegan',
                'photo' => '/uploads/plats/moelleux-vegan.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_SOJA, AllergeneFixtures::ALLERGENE_FRUITS_A_COQUE],
            ],
            [
                'reference' => self::PLAT_PAVLOVA,
                'titre' => 'Pavlova aux fruits rouges',
                'photo' => '/uploads/plats/pavlova-fruits-rouges.jpg',
                'allergenes' => [AllergeneFixtures::ALLERGENE_OEUFS, AllergeneFixtures::ALLERGENE_LAIT],
            ],
        ];

        foreach ($plats as $data) {
            $plat = new Plat();
            $plat->setTitrePlat($data['titre']);
            $plat->setPhoto($data['photo']);

            foreach ($data['allergenes'] as $allergeneRef) {
                $plat->addAllergene($this->getReference($allergeneRef, Allergene::class));
            }

            $manager->persist($plat);
            $this->addReference($data['reference'], $plat);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [AllergeneFixtures::class];
    }
}
