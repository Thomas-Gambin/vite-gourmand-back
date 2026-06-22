<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le lien de réinitialisation est invalide.')]
        public string $token,

        #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
        #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une majuscule.')]
        #[Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir au moins une minuscule.')]
        #[Assert\Regex(pattern: '/\\d/', message: 'Le mot de passe doit contenir au moins un chiffre.')]
        #[Assert\Regex(pattern: '/[^A-Za-z0-9]/', message: 'Le mot de passe doit contenir au moins un caractère spécial.')]
        public string $password,
    ) {
    }
}
