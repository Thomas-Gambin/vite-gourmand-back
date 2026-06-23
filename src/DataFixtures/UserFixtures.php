<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_JOSE = 'user_jose';
    public const USER_JULIE = 'user_julie';
    public const CLIENT_1 = 'client_1';
    public const CLIENT_2 = 'client_2';
    public const CLIENT_3 = 'client_3';
    public const CLIENT_4 = 'client_4';
    public const CLIENT_5 = 'client_5';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'reference' => self::USER_JOSE,
                'email' => 'jose@vitegourmand.fr',
                'nom' => 'Martin',
                'prenom' => 'José',
                'telephone' => '0600000001',
                'ville' => 'Bordeaux',
                'pays' => 'France',
                'adressePostale' => '12 rue Sainte-Catherine',
                'role' => RoleFixtures::ROLE_ADMIN,
            ],
            [
                'reference' => self::USER_JULIE,
                'email' => 'julie@vitegourmand.fr',
                'nom' => 'Martin',
                'prenom' => 'Julie',
                'telephone' => '0600000002',
                'ville' => 'Bordeaux',
                'pays' => 'France',
                'adressePostale' => '12 rue Sainte-Catherine',
                'role' => RoleFixtures::ROLE_EMPLOYEE,
            ],
            [
                'reference' => self::CLIENT_1,
                'email' => 'client1@example.com',
                'nom' => 'Durand',
                'prenom' => 'Claire',
                'telephone' => '0611223344',
                'ville' => 'Bordeaux',
                'pays' => 'France',
                'adressePostale' => '8 place Pey-Berland',
                'role' => RoleFixtures::ROLE_USER,
            ],
            [
                'reference' => self::CLIENT_2,
                'email' => 'client2@example.com',
                'nom' => 'Bernard',
                'prenom' => 'Lucas',
                'telephone' => '0622334455',
                'ville' => 'Talence',
                'pays' => 'France',
                'adressePostale' => '21 avenue de la Libération',
                'role' => RoleFixtures::ROLE_USER,
            ],
            [
                'reference' => self::CLIENT_3,
                'email' => 'client3@example.com',
                'nom' => 'Petit',
                'prenom' => 'Sophie',
                'telephone' => '0633445566',
                'ville' => 'Mérignac',
                'pays' => 'France',
                'adressePostale' => '4 rue du Jard',
                'role' => RoleFixtures::ROLE_USER,
            ],
            [
                'reference' => self::CLIENT_4,
                'email' => 'client4@example.com',
                'nom' => 'Moreau',
                'prenom' => 'Antoine',
                'telephone' => '0644556677',
                'ville' => 'Pessac',
                'pays' => 'France',
                'adressePostale' => '15 avenue Pasteur',
                'role' => RoleFixtures::ROLE_USER,
            ],
            [
                'reference' => self::CLIENT_5,
                'email' => 'client5@example.com',
                'nom' => 'Leroy',
                'prenom' => 'Emma',
                'telephone' => '0655667788',
                'ville' => 'Bordeaux',
                'pays' => 'France',
                'adressePostale' => '33 cours Victor Hugo',
                'role' => RoleFixtures::ROLE_USER,
            ],
        ];

        foreach ($users as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setTelephone($data['telephone']);
            $user->setVille($data['ville']);
            $user->setPays($data['pays']);
            $user->setAdressePostale($data['adressePostale']);
            $user->setRole($this->getReference($data['role'], Role::class));
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Password123!'));
            $user->setIsVerified(true);
            $user->setVerifiedAt(new \DateTimeImmutable());

            $manager->persist($user);
            $this->addReference($data['reference'], $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RoleFixtures::class];
    }
}
