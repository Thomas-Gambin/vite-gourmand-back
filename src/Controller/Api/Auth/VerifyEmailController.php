<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\VerifyEmailPayload;
use App\Repository\UserRepository;
use App\Service\Auth\EmailVerificationTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
final class VerifyEmailController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailVerificationTokenGenerator $verificationTokenGenerator,
    ) {}

    public function __invoke(#[MapRequestPayload] VerifyEmailPayload $payload): JsonResponse
    {
        $plain = trim($payload->token);
        if ('' === $plain) {
            return new JsonResponse([
                'code' => 'INVALID_TOKEN',
                'message' => 'Lien invalide ou expiré.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $hash = $this->verificationTokenGenerator->hashToken($plain);
        $user = $this->userRepository->findOneByEmailVerificationTokenHash($hash);

        if (null === $user) {
            return new JsonResponse([
                'code' => 'INVALID_TOKEN',
                'message' => 'Lien invalide ou expiré.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->isVerified()) {
            return new JsonResponse([
                'message' => 'Votre adresse email est confirmée. Vous pouvez vous connecter.',
            ], Response::HTTP_OK);
        }

        $expiresAt = $user->getEmailVerificationTokenExpiresAt();
        if (null === $expiresAt || $expiresAt < new \DateTimeImmutable()) {
            return new JsonResponse([
                'code' => 'TOKEN_EXPIRED',
                'message' => 'Le lien a expiré. Demandez un nouvel email de confirmation.',
            ], Response::HTTP_GONE);
        }

        $user->setIsVerified(true);
        $user->setVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Votre adresse email est confirmée. Vous pouvez vous connecter.',
        ], Response::HTTP_OK);
    }
}
