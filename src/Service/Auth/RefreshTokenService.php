<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class RefreshTokenService
{
    private const TTL_SECONDS = 2_592_000;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function generateForUser(User $user): string
    {
        $refreshToken = bin2hex(random_bytes(64));
        $expiresAt = new \DateTime('+'.self::TTL_SECONDS.' seconds');

        $tokenEntity = new RefreshToken();
        $tokenEntity->setUsername((string) $user->getEmail());
        $tokenEntity->setRefreshToken($refreshToken);
        $tokenEntity->setValid($expiresAt);

        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function getExpirationTimestamp(): int
    {
        return (new \DateTime('+'.self::TTL_SECONDS.' seconds'))->getTimestamp();
    }
}
