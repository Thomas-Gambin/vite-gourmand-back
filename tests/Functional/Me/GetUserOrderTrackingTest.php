<?php

declare(strict_types=1);

namespace App\Tests\Functional\Me;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Regime;
use App\Entity\Role;
use App\Entity\Theme;
use App\Entity\User;
use App\Service\CommandeHistoriqueService;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class GetUserOrderTrackingTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    public function testGetWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/me/orders/1/tracking');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetOrderOwnedByAnotherUserReturns404(): void
    {
        $client = static::createClient();
        $ownerEmail = $this->uniqueEmail('owner');
        $otherEmail = $this->uniqueEmail('other');
        $owner = $this->createVerifiedUser($client, $ownerEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $otherEmail, 'ROLE_USER');
        $commande = $this->createCommandeWithHistorique($client, $owner, CommandeStatus::ACCEPTE);
        $otherToken = $this->login($client, $otherEmail);

        $this->jsonRequest(
            $client,
            'GET',
            sprintf('/api/me/orders/%d/tracking', $commande->getId()),
            [],
            $otherToken,
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetPendingOrderReturns403(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('pending');
        $user = $this->createVerifiedUser($client, $email, 'ROLE_USER');
        $commande = $this->createCommandeWithHistorique($client, $user, CommandeStatus::EN_ATTENTE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'GET',
            sprintf('/api/me/orders/%d/tracking', $commande->getId()),
            [],
            $token,
        );

        self::assertResponseStatusCodeSame(403);
        $data = $this->json($client);
        self::assertSame('FORBIDDEN', $data['code'] ?? null);
    }

    public function testGetAcceptedOrderReturnsChronologicalTracking(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('accepted');
        $user = $this->createVerifiedUser($client, $email, 'ROLE_USER');
        $commande = $this->createCommandeWithHistorique($client, $user, CommandeStatus::ACCEPTE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'GET',
            sprintf('/api/me/orders/%d/tracking', $commande->getId()),
            [],
            $token,
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->json($client);
        self::assertSame(CommandeStatus::ACCEPTE, $data['tracking']['statutActuel'] ?? null);
        self::assertIsArray($data['tracking']['etapes'] ?? null);

        $etapes = $data['tracking']['etapes'];
        self::assertCount(2, $etapes);
        self::assertSame(CommandeStatus::EN_ATTENTE, $etapes[0]['statut'] ?? null);
        self::assertSame(CommandeStatus::ACCEPTE, $etapes[1]['statut'] ?? null);

        $dates = array_map(
            static fn (array $etape): int => strtotime((string) ($etape['dateModification'] ?? '')),
            $etapes,
        );
        self::assertLessThanOrEqual($dates[1], $dates[0] + 1);
    }

    public function testEmployeeStatusUpdateAppearsInTracking(): void
    {
        $client = static::createClient();
        $clientEmail = $this->uniqueEmail('client');
        $employeeEmail = $this->uniqueEmail('employee');
        $user = $this->createVerifiedUser($client, $clientEmail, 'ROLE_USER');
        $this->createVerifiedUser($client, $employeeEmail, 'ROLE_EMPLOYEE');
        $commande = $this->createCommandeWithHistorique($client, $user, CommandeStatus::ACCEPTE);
        $clientToken = $this->login($client, $clientEmail);
        $employeeToken = $this->login($client, $employeeEmail);

        $this->jsonRequest(
            $client,
            'PATCH',
            sprintf('/api/employee/commandes/%d/statut', $commande->getId()),
            ['statut' => CommandeStatus::EN_PREPARATION],
            $employeeToken,
        );
        self::assertResponseStatusCodeSame(200);

        $this->jsonRequest(
            $client,
            'GET',
            sprintf('/api/me/orders/%d/tracking', $commande->getId()),
            [],
            $clientToken,
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->json($client);
        self::assertSame(CommandeStatus::EN_PREPARATION, $data['tracking']['statutActuel'] ?? null);

        $statuts = array_column($data['tracking']['etapes'], 'statut');
        self::assertContains(CommandeStatus::EN_PREPARATION, $statuts);
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

    private function createCommandeWithHistorique(KernelBrowser $client, User $user, string $statut): Commande
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var CommandeHistoriqueService $historiqueService */
        $historiqueService = static::getContainer()->get(CommandeHistoriqueService::class);

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
        $menu->setDescription('Menu de test pour le suivi.');
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

        $chain = match ($statut) {
            CommandeStatus::EN_ATTENTE => [CommandeStatus::EN_ATTENTE],
            CommandeStatus::ACCEPTE => [CommandeStatus::EN_ATTENTE, CommandeStatus::ACCEPTE],
            default => [CommandeStatus::EN_ATTENTE, $statut],
        };

        $baseDate = $commande->getDateCommande() ?? new \DateTimeImmutable();
        foreach ($chain as $index => $step) {
            $historiqueService->record($commande, $step, $baseDate->modify(sprintf('+%d hours', $index)));
        }

        $entityManager->flush();

        return $commande;
    }
}
