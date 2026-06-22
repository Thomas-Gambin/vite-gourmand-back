<?php

declare(strict_types=1);

namespace App\Service\Auth;

final class EmailVerificationTokenGenerator
{
    public function __construct(
        private readonly int $ttlHours = 24,
    ) {}

    public function createPlainToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->modify(sprintf('+%d hours', $this->ttlHours));
    }
}
