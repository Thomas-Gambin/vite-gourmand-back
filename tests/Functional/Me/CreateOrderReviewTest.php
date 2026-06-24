<?php

declare(strict_types=1);

namespace App\Tests\Functional\Me;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Regime;
use App\Entity\Role;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\AvisRepository;
use App\Service\AvisStatus;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateOrderReviewTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    private function uniqueEmail(string $prefix): string
    {
        return sprintf('%s%d@ex.com', $prefix, random_int(100000, 999999));
    }

    public function testPostWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/me/orders/1/review', $this->validPayload());

        self::assertResponseStatusCodeSame(401);
    }

    public function testPostOrderOwnedByAnotherUserReturns404(): void
    {
        $client = static::createClient();
        $ownerEmail = $this->uniqueEmail('owner');
        $otherEmail = $this->uniqueEmail('other');
        $owner = $this->createVerifiedUser($client, $ownerEmail);
        $this->createVerifiedUser($client, $otherEmail);
        $commande = $this->createCommande($client, $owner, CommandeStatus::TERMINEE);
        $otherToken = $this->login($client, $otherEmail);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            $this->validPayload(),
            $otherToken,
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testPostNonTerminatedOrderReturns403(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('nterm');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::EN_ATTENTE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            $this->validPayload(),
            $token,
        );

        self::assertResponseStatusCodeSame(403);
        $data = $this->json($client);
        self::assertSame('FORBIDDEN', $data['code'] ?? null);
    }

    public function testPostAlreadyReviewedOrderReturns409(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('dup');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::TERMINEE);
        $this->createAvis($client, $user, $commande);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            $this->validPayload(),
            $token,
        );

        self::assertResponseStatusCodeSame(409);
        $data = $this->json($client);
        self::assertSame('CONFLICT', $data['code'] ?? null);
    }

    public function testPostInvalidNoteReturns422(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('note');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::TERMINEE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            ['note' => 0, 'commentaire' => 'Commentaire valide pour le test.'],
            $token,
        );

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertStringContainsString('note', strtolower($data['detail'] ?? ''));
    }

    public function testPostShortCommentReturns422(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('short');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::TERMINEE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            ['note' => 5, 'commentaire' => 'Court'],
            $token,
        );

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertStringContainsString('commentaire', strtolower($data['detail'] ?? ''));
    }

    public function testPostValidReviewCreatesPendingAvis(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('valid');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::TERMINEE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            $this->validPayload(),
            $token,
        );

        self::assertResponseStatusCodeSame(201);
        $data = $this->json($client);
        self::assertSame(AvisStatus::EN_ATTENTE, $data['review']['statut'] ?? null);
        self::assertSame(5, $data['review']['note'] ?? null);
        self::assertSame($commande->getId(), $data['review']['commandeId'] ?? null);

        /** @var AvisRepository $avisRepository */
        $avisRepository = static::getContainer()->get(AvisRepository::class);
        $avis = $avisRepository->findOneByCommande($commande);
        self::assertInstanceOf(Avis::class, $avis);
        self::assertSame(AvisStatus::EN_ATTENTE, $avis->getStatut());
        self::assertSame(5, $avis->getNote());
    }

    public function testStatutInPayloadIsIgnored(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('stat');
        $user = $this->createVerifiedUser($client, $email);
        $commande = $this->createCommande($client, $user, CommandeStatus::TERMINEE);
        $token = $this->login($client, $email);

        $this->jsonRequest(
            $client,
            'POST',
            sprintf('/api/me/orders/%d/review', $commande->getId()),
            [
                ...$this->validPayload(),
                'statut' => AvisStatus::VALIDE,
            ],
            $token,
        );

        self::assertResponseStatusCodeSame(201);
        $data = $this->json($client);
        self::assertSame(AvisStatus::EN_ATTENTE, $data['review']['statut'] ?? null);
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

    /**
     * @return array<string, int|string>
     */
    private function validPayload(): array
    {
        return [
            'note' => 5,
            'commentaire' => 'Très bon menu, livraison ponctuelle et plats excellents.',
        ];
    }

    private function createVerifiedUser(KernelBrowser $client, string $email): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $role = $entityManager->getRepository(Role::class)->findOneBy(['libelle' => 'ROLE_USER']);
        if (!$role instanceof Role) {
            $role = new Role();
            $role->setLibelle('ROLE_USER');
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
        $menu->setDescription('Menu de test pour les avis.');
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

    private function createAvis(KernelBrowser $client, User $user, Commande $commande): Avis
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $avis = new Avis();
        $avis->setNote(4);
        $avis->setDescription('Avis existant pour tester le conflit.');
        $avis->setStatut(AvisStatus::EN_ATTENTE);
        $avis->setUtilisateur($user);
        $avis->setCommande($commande);

        $entityManager->persist($avis);
        $entityManager->flush();

        return $avis;
    }
}
