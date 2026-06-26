<?php

declare(strict_types=1);

namespace App\Tests\Functional\Employee;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Regime;
use App\Entity\Role;
use App\Entity\Theme;
use App\Entity\User;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class EasyAdminAccessTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    public function testGuestIsRedirectedToLoginOnEmployeeDashboard(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }

    public function testClientCannotAccessEmployeeDashboard(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('client');
        $this->createVerifiedUser($client, $email, 'ROLE_USER');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEmployeeCanAccessEmployeeDashboard(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.content-panel h1', 'Bienvenue dans le back-office');
    }

    public function testAdminCanAccessEmployeeDashboard(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('admin');
        $this->createVerifiedUser($client, $email, 'ROLE_ADMIN');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCanAccessCommandeIndex(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin/commande');

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCanAccessCommandeChangeStatus(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $clientEmail = $this->uniqueEmail('client');
        $employee = $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $clientUser = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $commande = $this->createCommande($client, $clientUser, CommandeStatus::ACCEPTE);
        $client->loginUser($employee);

        $client->request('GET', sprintf('/admin/commande/%d/change-status', $commande->getId()));

        self::assertResponseIsSuccessful();
    }

    public function testEmployeeCanAccessCommandeDetail(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $clientEmail = $this->uniqueEmail('client');
        $employee = $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $clientUser = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $commande = $this->createCommande($client, $clientUser, CommandeStatus::ACCEPTE);
        $client->loginUser($employee);

        $client->request('GET', sprintf('/admin/commande/%d', $commande->getId()));

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Mode de contact', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Motif employé', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Date de contact', (string) $client->getResponse()->getContent());
    }

    public function testEmployeeCanAccessMenuIndex(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin/menu');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Theme #', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('vg-menu-plats-btn', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Inaccessible', (string) $client->getResponse()->getContent());
    }

    public function testEmployeeDashboardDoesNotShowAdminUserManagementMenu(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('http://localhost/admin/utilisateur', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('http://localhost/admin/employe', (string) $client->getResponse()->getContent());
    }

    private function createCommande(KernelBrowser $client, User $user, string $statut): Commande
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $suffix = (string) random_int(100000, 999999);
        $theme = new Theme();
        $theme->setLibelle('Theme test '.$suffix);
        $entityManager->persist($theme);

        $regime = new Regime();
        $regime->setLibelle('Regime test '.$suffix);
        $entityManager->persist($regime);

        $menu = new Menu();
        $menu->setTitre('Menu test '.$suffix);
        $menu->setNombrePersonneMinimum(2);
        $menu->setPrixParPersonne('25.00');
        $menu->setDescription('Menu de test employé.');
        $menu->setQuantiteRestante(10);
        $menu->setTheme($theme);
        $menu->setRegime($regime);
        $entityManager->persist($menu);

        $commande = new Commande();
        $commande->setNumeroCommande('CMD-TEST-'.$suffix);
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setDatePrestation(new \DateTimeImmutable('+7 days'));
        $commande->setHeureLivraison('12:00');
        $commande->setPrixMenu('100.00');
        $commande->setPrixLivraison('10.00');
        $commande->setNombrePersonne(4);
        $commande->setStatut($statut);
        $commande->setAdressePrestation('1 rue Test');
        $commande->setVillePrestation('Bordeaux');
        $commande->setCodePostalPrestation('33000');
        $commande->setUtilisateur($user);
        $commande->setMenu($menu);

        $entityManager->persist($commande);
        $entityManager->flush();

        return $commande;
    }

    private function uniqueEmail(string $prefix): string
    {
        return sprintf('%s-%s@example.com', $prefix, uniqid('', true));
    }

    private function createVerifiedUser(KernelBrowser $client, string $email, string $roleLibelle): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $role = $entityManager->getRepository(Role::class)->findOneBy(['libelle' => $roleLibelle]);
        if (!$role instanceof Role) {
            $role = new Role();
            $role->setLibelle($roleLibelle);
            $entityManager->persist($role);
            $entityManager->flush();
        }

        $user = new User();
        $user->setEmail($email);
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setTelephone('0600000000');
        $user->setVille('Bordeaux');
        $user->setPays('France');
        $user->setAdressePostale('1 rue Test');
        $user->setRole($role);
        $user->setPassword($passwordHasher->hashPassword($user, self::PASSWORD));
        $user->setIsVerified(true);
        $user->setVerifiedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function findUser(KernelBrowser $client, string $email): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }
}
