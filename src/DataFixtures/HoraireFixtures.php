<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Horaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HoraireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $horaires = [
            ['jour' => 'Lundi', 'ouverture' => 'Fermé', 'fermeture' => 'Fermé'],
            ['jour' => 'Mardi', 'ouverture' => '09:00', 'fermeture' => '18:00'],
            ['jour' => 'Mercredi', 'ouverture' => '09:00', 'fermeture' => '18:00'],
            ['jour' => 'Jeudi', 'ouverture' => '09:00', 'fermeture' => '18:00'],
            ['jour' => 'Vendredi', 'ouverture' => '09:00', 'fermeture' => '19:00'],
            ['jour' => 'Samedi', 'ouverture' => '09:00', 'fermeture' => '19:00'],
            ['jour' => 'Dimanche', 'ouverture' => '09:00', 'fermeture' => '12:30'],
        ];

        foreach ($horaires as $data) {
            $horaire = new Horaire();
            $horaire->setJour($data['jour']);
            $horaire->setHeureOuverture($data['ouverture']);
            $horaire->setHeureFermeture($data['fermeture']);
            $manager->persist($horaire);
        }

        $manager->flush();
    }
}
