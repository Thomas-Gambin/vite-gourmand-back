<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\ForgotPasswordPayload;
use App\Repository\UserRepository;
use App\Service\Auth\EmailVerificationTokenGenerator;
use App\Service\Mail\PasswordResetEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
final class ForgotPasswordController
{
    private const GENERIC_MESSAGE = 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'App\Service\Auth\PasswordResetTokenGenerator')]
        private readonly EmailVerificationTokenGenerator $passwordResetTokenGenerator,
        private readonly PasswordResetEmailService $passwordResetEmailService,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(#[MapRequestPayload] ForgotPasswordPayload $payload): JsonResponse
    {
        $email = strtolower(trim($payload->email));
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            return new JsonResponse([
                'message' => self::GENERIC_MESSAGE,
            ], Response::HTTP_OK);
        }

        $plainToken = $this->passwordResetTokenGenerator->createPlainToken();
        $user->setPasswordResetToken($this->passwordResetTokenGenerator->hashToken($plainToken));
        $user->setPasswordResetTokenExpiresAt($this->passwordResetTokenGenerator->expiresAt());
        $this->entityManager->flush();

        try {
            $this->passwordResetEmailService->send(
                toEmail: $user->getEmail(),
                prenom: $user->getPrenom() ?? '',
                plainToken: $plainToken,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Password reset email failed.', [
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
