<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AvisFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $avis = [
            [
                'user' => UserFixtures::CLIENT_1,
                'note' => 5,
                'description' => 'Menu excellent, présentation soignée et livraison ponctuelle.',
                'statut' => 'valide',
            ],
            [
                'user' => UserFixtures::CLIENT_2,
                'note' => 4,
                'description' => 'Très bonne prestation pour notre repas de famille.',
                'statut' => 'valide',
            ],
            [
                'user' => UserFixtures::CLIENT_3,
                'note' => 5,
                'description' => 'Les plats étaient frais, généreux et très bien préparés.',
                'statut' => 'valide',
            ],
            [
                'user' => UserFixtures::CLIENT_4,
                'note' => 3,
                'description' => 'Bon repas mais livraison un peu tardive.',
                'statut' => 'en_attente',
            ],
            [
                'user' => UserFixtures::CLIENT_5,
                'note' => 4,
                'description' => 'Très bon buffet pour notre événement professionnel.',
                'statut' => 'valide',
            ],
            [
                'user' => UserFixtures::CLIENT_2,
                'note' => 2,
                'description' => 'Avis à modérer pour tester le refus côté back-office.',
                'statut' => 'refuse',
            ],
        ];

        foreach ($avis as $data) {
            $avisEntity = new Avis();
            $avisEntity->setNote($data['note']);
            $avisEntity->setDescription($data['description']);
            $avisEntity->setStatut($data['statut']);
            $avisEntity->setUtilisateur($this->getReference($data['user'], User::class));

            $manager->persist($avisEntity);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
