<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class VerifyEmailPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le jeton est obligatoire.')]
        public readonly string $token,
    ) {}
}
