<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Le nom doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $nom,

        #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Le prénom doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $prenom,

        #[Assert\NotBlank(message: "L'email est obligatoire.")]
        #[Assert\Email(message: "L'email n'est pas valide.")]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
        #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une majuscule.')]
        #[Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir au moins une minuscule.')]
        #[Assert\Regex(pattern: '/\\d/', message: 'Le mot de passe doit contenir au moins un chiffre.')]
        #[Assert\Regex(pattern: '/[^A-Za-z0-9]/', message: 'Le mot de passe doit contenir au moins un caractère spécial.')]
        public readonly string $password,

        #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Le téléphone doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $telephone,

        #[Assert\NotBlank(message: 'La ville est obligatoire.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'La ville doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $ville,

        #[Assert\NotBlank(message: 'Le pays est obligatoire.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Le pays doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $pays,

        #[Assert\NotBlank(message: "L'adresse postale est obligatoire.")]
        #[Assert\Length(
            max: 50,
            maxMessage: "L'adresse postale doit contenir au maximum {{ limit }} caractères."
        )]
        public readonly string $adressePostale,
    ) {}
}
