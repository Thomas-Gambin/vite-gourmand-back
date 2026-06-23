<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use App\Entity\User;

final readonly class UserProfileDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $nom,
        public string $prenom,
        public string $telephone,
        public string $adressePostale,
        public string $ville,
        public string $pays,
        public array $roles,
        public bool $isVerified,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            id: (int) $user->getId(),
            email: (string) $user->getEmail(),
            nom: (string) $user->getNom(),
            prenom: (string) $user->getPrenom(),
            telephone: (string) $user->getTelephone(),
            adressePostale: (string) $user->getAdressePostale(),
            ville: (string) $user->getVille(),
            pays: (string) $user->getPays(),
            roles: $user->getRoles(),
            isVerified: $user->isVerified(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'adressePostale' => $this->adressePostale,
            'ville' => $this->ville,
            'pays' => $this->pays,
            'roles' => $this->roles,
            'isVerified' => $this->isVerified,
        ];
    }
}
