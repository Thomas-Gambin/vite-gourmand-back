<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminUserPersistenceService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function getRoleByLibelle(string $libelle): Role
    {
        $role = $this->roleRepository->findOneBy(['libelle' => $libelle]);
        if (!$role instanceof Role) {
            throw new \RuntimeException(sprintf('Le rôle « %s » est introuvable.', $libelle));
        }

        return $role;
    }

    public function prepareNewUser(User $user, string $plainPassword, string $roleLibelle): void
    {
        $user->setRole($this->getRoleByLibelle($roleLibelle));
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->markAsVerified($user);
    }

    public function updateUserPassword(User $user, ?string $plainPassword): void
    {
        if ($plainPassword === null || $plainPassword === '') {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    }

    public function markAsVerified(User $user): void
    {
        $user->setIsVerified(true);
        $user->setVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);
    }

    public function promoteToEmployee(User $user): void
    {
        if ($user->getRole()?->getLibelle() !== 'ROLE_USER') {
            throw new \InvalidArgumentException('Seuls les comptes clients peuvent être promus en employé.');
        }

        $user->setRole($this->getRoleByLibelle('ROLE_EMPLOYEE'));
    }

    public function assertDeletable(User $user, User $currentAdmin): void
    {
        if ($user->getId() === $currentAdmin->getId()) {
            throw new \InvalidArgumentException('Vous ne pouvez pas supprimer votre propre compte.');
        }

        if (!$user->getCommandes()->isEmpty()) {
            throw new \InvalidArgumentException('Impossible de supprimer cet utilisateur : des commandes y sont associées.');
        }

        if (!$user->getAvis()->isEmpty()) {
            throw new \InvalidArgumentException('Impossible de supprimer cet utilisateur : des avis y sont associés.');
        }
    }
}
