<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\User;
use App\Service\OrderPriceCalculator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommandeFixtures extends Fixture implements DependentFixtureInterface
{
    private readonly OrderPriceCalculator $priceCalculator;

    public function __construct()
    {
        $this->priceCalculator = new OrderPriceCalculator();
    }

    public function load(ObjectManager $manager): void
    {
        $commandes = [
            [
                'reference' => 'commande_0001',
                'numero' => 'CMD-2026-0001',
                'user' => UserFixtures::CLIENT_1,
                'menu' => MenuFixtures::MENU_NOEL,
                'dateCommande' => '2026-11-20',
                'datePrestation' => '2026-12-24',
                'heureLivraison' => '18:30',
                'nombrePersonne' => 8,
                'prixLivraison' => '0.00',
                'statut' => 'terminee',
                'pretMateriel' => false,
                'restitutionMateriel' => true,
            ],
            [
                'reference' => 'commande_0002',
                'numero' => 'CMD-2026-0002',
                'user' => UserFixtures::CLIENT_2,
                'menu' => MenuFixtures::MENU_PAQUES,
                'dateCommande' => '2026-03-12',
                'datePrestation' => '2026-04-05',
                'heureLivraison' => '12:00',
                'nombrePersonne' => 6,
                'prixLivraison' => '12.08',
                'statut' => 'terminee',
                'pretMateriel' => true,
                'restitutionMateriel' => true,
            ],
            [
                'reference' => 'commande_0003',
                'numero' => 'CMD-2026-0003',
                'user' => UserFixtures::CLIENT_3,
                'menu' => MenuFixtures::MENU_CLASSIQUE_BORDELAIS,
                'dateCommande' => '2026-05-01',
                'datePrestation' => '2026-05-10',
                'heureLivraison' => '20:00',
                'nombrePersonne' => 4,
                'prixLivraison' => '16.80',
                'statut' => 'livre',
                'pretMateriel' => false,
                'restitutionMateriel' => true,
            ],
            [
                'reference' => 'commande_0004',
                'numero' => 'CMD-2026-0004',
                'user' => UserFixtures::CLIENT_4,
                'menu' => MenuFixtures::MENU_VEGETARIEN,
                'dateCommande' => '2026-05-08',
                'datePrestation' => '2026-05-18',
                'heureLivraison' => '19:30',
                'nombrePersonne' => 5,
                'prixLivraison' => '10.90',
                'statut' => 'en_preparation',
                'pretMateriel' => false,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0005',
                'numero' => 'CMD-2026-0005',
                'user' => UserFixtures::CLIENT_5,
                'menu' => MenuFixtures::MENU_VEGAN,
                'dateCommande' => '2026-05-12',
                'datePrestation' => '2026-05-22',
                'heureLivraison' => '12:30',
                'nombrePersonne' => 9,
                'prixLivraison' => '0.00',
                'statut' => 'accepte',
                'pretMateriel' => false,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0006',
                'numero' => 'CMD-2026-0006',
                'user' => UserFixtures::CLIENT_1,
                'menu' => MenuFixtures::MENU_MARIAGE,
                'dateCommande' => '2026-06-01',
                'datePrestation' => '2026-07-15',
                'heureLivraison' => '17:00',
                'nombrePersonne' => 30,
                'prixLivraison' => '24.47',
                'statut' => 'en_attente_retour_materiel',
                'pretMateriel' => true,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0007',
                'numero' => 'CMD-2026-0007',
                'user' => UserFixtures::CLIENT_2,
                'menu' => MenuFixtures::MENU_ENTREPRISE,
                'dateCommande' => '2026-06-05',
                'datePrestation' => '2026-06-20',
                'heureLivraison' => '11:30',
                'nombrePersonne' => 15,
                'prixLivraison' => '0.00',
                'statut' => 'en_cours_de_livraison',
                'pretMateriel' => true,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0008',
                'numero' => 'CMD-2026-0008',
                'user' => UserFixtures::CLIENT_3,
                'menu' => MenuFixtures::MENU_ANNIVERSAIRE,
                'dateCommande' => '2026-06-10',
                'datePrestation' => '2026-06-28',
                'heureLivraison' => '19:00',
                'nombrePersonne' => 10,
                'prixLivraison' => '18.12',
                'statut' => 'en_attente',
                'pretMateriel' => false,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0009',
                'numero' => 'CMD-2026-0009',
                'user' => UserFixtures::CLIENT_4,
                'menu' => MenuFixtures::MENU_CLASSIQUE_BORDELAIS,
                'dateCommande' => '2026-06-15',
                'datePrestation' => '2026-06-25',
                'heureLivraison' => '20:00',
                'nombrePersonne' => 2,
                'prixLivraison' => '8.54',
                'statut' => 'annulee',
                'pretMateriel' => false,
                'restitutionMateriel' => false,
            ],
            [
                'reference' => 'commande_0010',
                'numero' => 'CMD-2026-0010',
                'user' => UserFixtures::CLIENT_5,
                'menu' => MenuFixtures::MENU_ENTREPRISE,
                'dateCommande' => '2026-06-18',
                'datePrestation' => '2026-07-02',
                'heureLivraison' => '12:00',
                'nombrePersonne' => 25,
                'prixLivraison' => '14.44',
                'statut' => 'terminee',
                'pretMateriel' => true,
                'restitutionMateriel' => true,
            ],
        ];

        foreach ($commandes as $data) {
            $menu = $this->getReference($data['menu'], Menu::class);

            $commande = new Commande();
            $commande->setNumeroCommande($data['numero']);
            $commande->setDateCommande(new \DateTimeImmutable($data['dateCommande']));
            $commande->setDatePrestation(new \DateTimeImmutable($data['datePrestation']));
            $commande->setHeureLivraison($data['heureLivraison']);
            $commande->setNombrePersonne($data['nombrePersonne']);
            $breakdown = $this->priceCalculator->calculate(
                $menu,
                $data['nombrePersonne'],
                $data['adressePrestation'] ?? '1 rue de la République',
                $data['villePrestation'] ?? 'Bordeaux',
                $data['codePostalPrestation'] ?? '33000',
            );
            $commande->setPrixMenu($breakdown->prixMenu);
            $commande->setPrixLivraison($data['prixLivraison']);
            $commande->setStatut($data['statut']);
            $commande->setPretMateriel($data['pretMateriel']);
            $commande->setRestitutionMateriel($data['restitutionMateriel']);
            $commande->setAdressePrestation($data['adressePrestation'] ?? '1 rue de la République');
            $commande->setVillePrestation($data['villePrestation'] ?? 'Bordeaux');
            $commande->setCodePostalPrestation($data['codePostalPrestation'] ?? '33000');
            $commande->setUtilisateur($this->getReference($data['user'], User::class));
            $commande->setMenu($menu);

            $manager->persist($commande);
            $this->addReference($data['reference'], $commande);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MenuFixtures::class,
        ];
    }
}
