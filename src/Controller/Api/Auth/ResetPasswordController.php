<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\ResetPasswordPayload;
use App\Repository\UserRepository;
use App\Service\Auth\EmailVerificationTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
final class ResetPasswordController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'App\Service\Auth\PasswordResetTokenGenerator')]
        private readonly EmailVerificationTokenGenerator $passwordResetTokenGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(#[MapRequestPayload] ResetPasswordPayload $payload): JsonResponse
    {
        $plain = trim($payload->token);
        if ('' === $plain) {
            return new JsonResponse([
                'code' => 'INVALID_TOKEN',
                'message' => 'Lien invalide ou expiré.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $hash = $this->passwordResetTokenGenerator->hashToken($plain);
        $user = $this->userRepository->findOneByPasswordResetTokenHash($hash);

        if (null === $user) {
            return new JsonResponse([
                'code' => 'INVALID_TOKEN',
                'message' => 'Lien invalide ou expiré.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $expiresAt = $user->getPasswordResetTokenExpiresAt();
        if (null === $expiresAt || $expiresAt < new \DateTimeImmutable()) {
            $user->setPasswordResetToken(null);
            $user->setPasswordResetTokenExpiresAt(null);
            $this->entityManager->flush();

            return new JsonResponse([
                'code' => 'TOKEN_EXPIRED',
                'message' => 'Le lien a expiré. Demandez une nouvelle réinitialisation.',
            ], Response::HTTP_GONE);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $payload->password));
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.',
        ], Response::HTTP_OK);
    }
}
