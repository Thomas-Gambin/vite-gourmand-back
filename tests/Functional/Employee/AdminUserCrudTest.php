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

final class AdminUserCrudTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    public function testEmployeeCannotAccessUtilisateurIndex(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin/utilisateur');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEmployeeCannotAccessEmployeIndex(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $email, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin/employe');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessUtilisateurAndEmployeIndexes(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('admin');
        $this->createVerifiedUser($client, $email, 'ROLE_ADMIN');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin/utilisateur');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/admin/employe');
        self::assertResponseIsSuccessful();
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

    public function testAdminDashboardShowsUserManagementMenu(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('admin');
        $this->createVerifiedUser($client, $email, 'ROLE_ADMIN');
        $client->loginUser($this->findUser($client, $email));

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('http://localhost/admin/utilisateur', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('http://localhost/admin/employe', (string) $client->getResponse()->getContent());
    }

    public function testUtilisateurIndexOnlyListsClients(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $clientEmail = $this->uniqueEmail('client');
        $employeeEmail = $this->uniqueEmail('employee');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $adminEmail));

        $client->request('GET', '/admin/utilisateur', ['query' => $clientEmail]);

        self::assertResponseIsSuccessful();
        $emailsInTable = $this->extractEmailsFromIndexTable($client);
        self::assertContains($clientEmail, $emailsInTable);

        $client->request('GET', '/admin/utilisateur', ['query' => $employeeEmail]);
        self::assertResponseIsSuccessful();
        self::assertNotContains($employeeEmail, $this->extractEmailsFromIndexTable($client));

        $client->request('GET', '/admin/utilisateur', ['query' => $adminEmail]);
        self::assertResponseIsSuccessful();
        self::assertNotContains($adminEmail, $this->extractEmailsFromIndexTable($client));
    }

    public function testEmployeIndexOnlyListsEmployees(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $clientEmail = $this->uniqueEmail('client');
        $employeeEmail = $this->uniqueEmail('employee');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $client->loginUser($this->findUser($client, $adminEmail));

        $client->request('GET', '/admin/employe', ['query' => $employeeEmail]);

        self::assertResponseIsSuccessful();
        $emailsInTable = $this->extractEmailsFromIndexTable($client);
        self::assertContains($employeeEmail, $emailsInTable);

        $client->request('GET', '/admin/employe', ['query' => $clientEmail]);
        self::assertResponseIsSuccessful();
        self::assertNotContains($clientEmail, $this->extractEmailsFromIndexTable($client));

        $client->request('GET', '/admin/employe', ['query' => $adminEmail]);
        self::assertResponseIsSuccessful();
        self::assertNotContains($adminEmail, $this->extractEmailsFromIndexTable($client));
    }

    public function testAdminCanCreateVerifiedUtilisateur(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $newClientEmail = $this->uniqueEmail('new-client');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $client->loginUser($this->findUser($client, $adminEmail));

        $this->submitNewUserForm($client, '/admin/utilisateur/new', $newClientEmail);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $createdUser = $this->findUser($client, $newClientEmail);
        self::assertTrue($createdUser->isVerified());
        self::assertNotNull($createdUser->getVerifiedAt());
        self::assertSame('ROLE_USER', $createdUser->getRole()?->getLibelle());
        self::assertNull($createdUser->getEmailVerificationToken());
    }

    public function testAdminCanCreateVerifiedEmploye(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $newEmployeeEmail = $this->uniqueEmail('new-employee');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $client->loginUser($this->findUser($client, $adminEmail));

        $this->submitNewUserForm($client, '/admin/employe/new', $newEmployeeEmail);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $createdUser = $this->findUser($client, $newEmployeeEmail);
        self::assertTrue($createdUser->isVerified());
        self::assertNotNull($createdUser->getVerifiedAt());
        self::assertSame('ROLE_EMPLOYEE', $createdUser->getRole()?->getLibelle());
    }

    public function testAdminCanPromoteClientToEmployee(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $clientEmail = $this->uniqueEmail('client');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $clientUser = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $client->loginUser($this->findUser($client, $adminEmail));

        $client->request('GET', sprintf('/admin/utilisateur/%d/promote-to-employee', $clientUser->getId()));

        self::assertResponseRedirects('/admin/employe');

        $promotedUser = $this->findUser($client, $clientEmail);
        self::assertSame('ROLE_EMPLOYEE', $promotedUser->getRole()?->getLibelle());
        self::assertTrue($promotedUser->isVerified());

        $client->request('GET', '/admin/utilisateur', ['query' => $clientEmail]);
        self::assertResponseIsSuccessful();
        self::assertNotContains($clientEmail, $this->extractEmailsFromIndexTable($client));

        $client->request('GET', '/admin/employe', ['query' => $clientEmail]);
        self::assertResponseIsSuccessful();
        self::assertContains($clientEmail, $this->extractEmailsFromIndexTable($client));
    }

    public function testAdminCannotDeleteOwnAccount(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $admin = $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $client->loginUser($admin);

        $this->submitDelete($client, '/admin/utilisateur/'.$admin->getId().'/delete');

        self::assertGreaterThanOrEqual(400, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->findUser($client, $adminEmail)->getId());
    }

    public function testAdminCanDeleteClientWithoutOrders(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $clientEmail = $this->uniqueEmail('client');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $clientUser = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $client->loginUser($this->findUser($client, $adminEmail));

        $this->submitDelete($client, '/admin/utilisateur/'.$clientUser->getId().'/delete');

        self::assertResponseRedirects();
        self::assertNull($this->findUserOrNull($client, $clientEmail));
    }

    public function testAdminCannotDeleteClientWithOrders(): void
    {
        $client = static::createClient();
        $adminEmail = $this->uniqueEmail('admin');
        $clientEmail = $this->uniqueEmail('client');

        $this->createVerifiedUser($client, $adminEmail, 'ROLE_ADMIN');
        $clientUser = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createCommande($client, $clientUser, CommandeStatus::ACCEPTE);
        $client->loginUser($this->findUser($client, $adminEmail));

        $this->submitDelete($client, '/admin/utilisateur/'.$clientUser->getId().'/delete');

        self::assertGreaterThanOrEqual(400, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->findUser($client, $clientEmail)->getId());
    }

    private function submitNewUserForm(KernelBrowser $client, string $url, string $email): void
    {
        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="User"]')->form([
            'User[email]' => $email,
            'User[nom]' => 'Dupont',
            'User[prenom]' => 'Jean',
            'User[telephone]' => '0612345678',
            'User[ville]' => 'Bordeaux',
            'User[pays]' => 'France',
            'User[adressePostale]' => '1 rue Test',
            'User[plainPassword]' => self::PASSWORD,
        ]);

        $client->submit($form);
    }

    private function submitDelete(KernelBrowser $client, string $url): void
    {
        $client->catchExceptions(true);

        $crawler = $client->request('GET', '/admin/utilisateur');
        self::assertResponseIsSuccessful();

        $tokenField = $crawler->filter('input[name="token"]');
        self::assertGreaterThan(0, $tokenField->count());

        $client->request('POST', $url, [
            'token' => $tokenField->attr('value'),
        ]);
    }

    /**
     * @return list<string>
     */
    private function extractEmailsFromIndexTable(KernelBrowser $client): array
    {
        $emails = [];

        $client->getCrawler()->filter('td[data-column="email"]')->each(function ($cell) use (&$emails): void {
            $email = trim($cell->text());
            if ($email !== '') {
                $emails[] = $email;
            }
        });

        return $emails;
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
        $user = $this->findUserOrNull($client, $email);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function findUserOrNull(KernelBrowser $client, string $email): ?User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        return $user instanceof User ? $user : null;
    }
}
