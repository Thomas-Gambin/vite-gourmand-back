<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const ROLE_USER = 'role_user';
    public const ROLE_EMPLOYEE = 'role_employee';
    public const ROLE_ADMIN = 'role_admin';

    public function load(ObjectManager $manager): void
    {
        $roles = [
            self::ROLE_USER => 'ROLE_USER',
            self::ROLE_EMPLOYEE => 'ROLE_EMPLOYEE',
            self::ROLE_ADMIN => 'ROLE_ADMIN',
        ];

        foreach ($roles as $reference => $libelle) {
            $role = new Role();
            $role->setLibelle($libelle);
            $manager->persist($role);
            $this->addReference($reference, $role);
        }

        $manager->flush();
    }
}
