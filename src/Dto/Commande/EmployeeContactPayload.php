<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeContactPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le mode de contact est obligatoire.')]
        #[Assert\Choice(choices: ['gsm', 'email'], message: 'Le mode de contact est invalide.')]
        public readonly ?string $contactMode = null,
        #[Assert\NotBlank(message: 'Le motif est obligatoire.')]
        #[Assert\Length(min: 3, minMessage: 'Le motif doit contenir au moins {{ limit }} caractères.')]
        public readonly ?string $employeeActionReason = null,
        #[Assert\NotNull(message: 'La date de contact est obligatoire.')]
        public readonly ?\DateTimeImmutable $contactedAt = null,
    ) {
    }
}
