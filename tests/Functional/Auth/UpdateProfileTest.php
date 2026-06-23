<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UpdateProfileTest extends WebTestCase
{
    private const PASSWORD = 'Password123!';

    private function uniqueEmail(string $prefix): string
    {
        return sprintf('%s%d@ex.com', $prefix, random_int(100000, 999999));
    }

    public function testPatchWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'PATCH', '/api/me', $this->validPayload());

        self::assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCanUpdateProfile(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('upd');
        $this->createVerifiedUser($client, $email);
        $token = $this->login($client, $email);

        $payload = [
            ...$this->validPayload(),
            'nom' => 'NouveauNom',
            'prenom' => 'NouveauPrenom',
            'telephone' => '0612345678',
        ];

        $this->jsonRequest($client, 'PATCH', '/api/me', $payload, $token);

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertStringContainsString('Vos informations personnelles ont bien été mises à jour.', $data['message'] ?? '');
        self::assertSame('NouveauNom', $data['user']['nom'] ?? null);
        self::assertSame('NouveauPrenom', $data['user']['prenom'] ?? null);
        self::assertSame('0612345678', $data['user']['telephone'] ?? null);
        self::assertSame($email, $data['user']['email'] ?? null);
        self::assertArrayNotHasKey('password', $data['user'] ?? []);
    }

    public function testEmailInPayloadIsIgnored(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('mail');
        $this->createVerifiedUser($client, $email);
        $token = $this->login($client, $email);

        $this->jsonRequest($client, 'PATCH', '/api/me', [
            ...$this->validPayload(),
            'email' => $this->uniqueEmail('hack'),
        ], $token);

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertSame($email, $data['user']['email'] ?? null);
        self::assertTrue($data['user']['isVerified'] ?? false);
    }

    public function testForbiddenFieldsInPayloadAreIgnored(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('forb');
        $this->createVerifiedUser($client, $email);
        $token = $this->login($client, $email);

        $this->jsonRequest($client, 'PATCH', '/api/me', [
            ...$this->validPayload(),
            'role' => 'ROLE_ADMIN',
            'password' => 'HackedPass1!',
            'id' => 9999,
        ], $token);

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertContains('ROLE_USER', $data['user']['roles'] ?? []);
        self::assertArrayNotHasKey('password', $data['user'] ?? []);
    }

    public function testInvalidPhoneReturnsValidationError(): void
    {
        $client = static::createClient();
        $email = $this->uniqueEmail('tel');
        $this->createVerifiedUser($client, $email);
        $token = $this->login($client, $email);

        $payload = [
            ...$this->validPayload(),
            'telephone' => '123',
        ];

        $this->jsonRequest($client, 'PATCH', '/api/me', $payload, $token);

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertSame('Le numéro de téléphone est invalide.', $data['detail'] ?? null);
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
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'nom' => 'Gambin',
            'prenom' => 'Thomas',
            'telephone' => '0600000000',
            'adressePostale' => '12 rue Exemple',
            'ville' => 'Bordeaux',
            'pays' => 'France',
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
}
