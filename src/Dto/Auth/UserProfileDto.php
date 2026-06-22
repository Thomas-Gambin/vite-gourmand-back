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
            'roles' => $this->roles,
            'isVerified' => $this->isVerified,
        ];
    }
}
