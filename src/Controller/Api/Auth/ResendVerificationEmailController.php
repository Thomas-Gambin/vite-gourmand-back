<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\ResendVerificationPayload;
use App\Repository\UserRepository;
use App\Service\Auth\EmailVerificationTokenGenerator;
use App\Service\Mail\ConfirmRegistrationEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
final class ResendVerificationEmailController
{
    private const GENERIC_MESSAGE = 'Si un compte non vérifié existe avec cet email, un nouvel email a été envoyé.';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailVerificationTokenGenerator $verificationTokenGenerator,
        private readonly ConfirmRegistrationEmailService $confirmRegistrationEmailService,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(#[MapRequestPayload] ResendVerificationPayload $payload): JsonResponse
    {
        $email = strtolower(trim($payload->email));
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user || $user->isVerified()) {
            return new JsonResponse([
                'message' => self::GENERIC_MESSAGE,
            ], Response::HTTP_OK);
        }

        $plainToken = $this->verificationTokenGenerator->createPlainToken();
        $user->setEmailVerificationToken($this->verificationTokenGenerator->hashToken($plainToken));
        $user->setEmailVerificationTokenExpiresAt($this->verificationTokenGenerator->expiresAt());
        $this->entityManager->flush();

        try {
            $this->confirmRegistrationEmailService->send(
                toEmail: $user->getEmail(),
                prenom: $user->getPrenom() ?? '',
                plainToken: $plainToken,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Resend verification email failed.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        }

        return new JsonResponse([
            'message' => self::GENERIC_MESSAGE,
        ], Response::HTTP_OK);
    }
}
