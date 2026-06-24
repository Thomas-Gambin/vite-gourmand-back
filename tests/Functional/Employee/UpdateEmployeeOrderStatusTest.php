<?php

declare(strict_types=1);

namespace App\Tests\Functional\Employee;

use App\Entity\Commande;
use App\Entity\CommandeHistoriqueStatut;
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

final class UpdateEmployeeOrderStatusTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    public function testPatchWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $this->jsonRequest(
            $client,
            'PATCH',
            '/api/employee/commandes/1/statut',
            ['statut' => CommandeStatus::EN_PREPARATION],
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testPatchAsClientReturns403(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('client');
        $user = $this->createVerifiedUser($client, $email, 'ROLE_USER');
        $commande = $this->createCommande($client, $user, CommandeStatus::ACCEPTE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'PATCH',
            sprintf('/api/employee/commandes/%d/statut', $commande->getId()),
            ['statut' => CommandeStatus::EN_PREPARATION],
            $token,
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testPatchAsEmployeeUpdatesStatusAndHistory(): void
    {
        $client = static::createClient();
        $clientEmail = $this->uniqueEmail('client');
        $employeeEmail = $this->uniqueEmail('employee');
        $user = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $commande = $this->createCommande($client, $user, CommandeStatus::ACCEPTE);
        $token = $this->login($client, $employeeEmail);

        $this->jsonRequest(
            $client,
            'PATCH',
            sprintf('/api/employee/commandes/%d/statut', $commande->getId()),
            ['statut' => CommandeStatus::EN_PREPARATION],
            $token,
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->json($client);
        self::assertSame(CommandeStatus::EN_PREPARATION, $data['order']['statut'] ?? null);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $historique = $entityManager->getRepository(CommandeHistoriqueStatut::class)->findBy(
            ['commande' => $commande],
            ['dateModification' => 'ASC'],
        );

        self::assertNotEmpty($historique);
        self::assertSame(
            CommandeStatus::EN_PREPARATION,
            $historique[array_key_last($historique)]->getStatut(),
        );
    }

    public function testPatchForwardJumpRecordsEachStatutAsSeparateHistoryEntry(): void
    {
        $client = static::createClient();
        $clientEmail = $this->uniqueEmail('client');
        $employeeEmail = $this->uniqueEmail('employee');
        $user = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $commande = $this->createCommande($client, $user, CommandeStatus::ACCEPTE);
        $token = $this->login($client, $employeeEmail);

        $this->jsonRequest(
            $client,
            'PATCH',
            sprintf('/api/employee/commandes/%d/statut', $commande->getId()),
            ['statut' => CommandeStatus::EN_COURS_DE_LIVRAISON],
            $token,
        );

        self::assertResponseStatusCodeSame(200);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $historique = $entityManager->getRepository(CommandeHistoriqueStatut::class)->findBy(
            ['commande' => $commande],
            ['dateModification' => 'ASC'],
        );

        self::assertCount(2, $historique);
        self::assertSame(CommandeStatus::EN_PREPARATION, $historique[0]->getStatut());
        self::assertSame(CommandeStatus::EN_COURS_DE_LIVRAISON, $historique[1]->getStatut());
    }

    public function testPatchInvalidStatusReturns422(): void
    {
        $client = static::createClient();
        $employeeEmail = $this->uniqueEmail('employee');
        $clientEmail = $this->uniqueEmail('client');
        $user = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $commande = $this->createCommande($client, $user, CommandeStatus::ACCEPTE);
        $token = $this->login($client, $employeeEmail);

        $this->jsonRequest(
            $client,
            'PATCH',
            sprintf('/api/employee/commandes/%d/statut', $commande->getId()),
            ['statut' => 'statut_invalide'],
            $token,
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testPatchUnknownOrderReturns404(): void
    {
        $client = static::createClient();
        $employeeEmail = $this->uniqueEmail('employee');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $token = $this->login($client, $employeeEmail);

        $this->jsonRequest(
            $client,
            'PATCH',
            '/api/employee/commandes/999999/statut',
            ['statut' => CommandeStatus::EN_PREPARATION],
            $token,
        );

        self::assertResponseStatusCodeSame(404);
    }

    private function uniqueEmail(string $prefix): string
    {
        return sprintf('%s%d@ex.com', $prefix, random_int(100000, 999999));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonRequest(
        KernelBrowser $client,
        string $method,
        string $uri,
        array $payload = [],
        ?string $token = null,
    ): void {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token !== null) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $client->request(
            $method,
            $uri,
            server: $headers,
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function json(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
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

    private function login(KernelBrowser $client, string $email): string
    {
        $client->request(
            'POST',
            '/api/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => self::PASSWORD,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertIsString($data['token'] ?? null);

        return $data['token'];
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
}
