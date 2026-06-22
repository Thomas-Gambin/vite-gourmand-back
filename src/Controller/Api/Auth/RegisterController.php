<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\RegisterPayload;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\Auth\EmailVerificationTokenGenerator;
use App\Service\Mail\ConfirmRegistrationEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
final class RegisterController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailVerificationTokenGenerator $verificationTokenGenerator,
        private readonly ConfirmRegistrationEmailService $confirmRegistrationEmailService,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(#[MapRequestPayload] RegisterPayload $payload): JsonResponse
    {
        $email = strtolower(trim($payload->email));

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            return $this->errorResponse(
                message: 'Cet email est déjà utilisé.',
                fields: ['email' => 'Cet email est déjà utilisé.'],
                status: Response::HTTP_CONFLICT
            );
        }

        $userRole = $this->roleRepository->findOneBy(['libelle' => 'ROLE_USER']);
        if ($userRole === null) {
            $this->logger->error('Default role ROLE_USER is missing in database.');

            return new JsonResponse([
                'code' => 'SERVER_ERROR',
                'message' => 'Inscription temporairement indisponible.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setNom(trim($payload->nom));
        $user->setPrenom(trim($payload->prenom));
        $user->setTelephone(trim($payload->telephone));
        $user->setVille(trim($payload->ville));
        $user->setPays(trim($payload->pays));
        $user->setAdressePostale(trim($payload->adressePostale));
        $user->setRole($userRole);
        $user->setPassword($this->passwordHasher->hashPassword($user, $payload->password));
        $user->setIsVerified(false);

        $plainToken = $this->verificationTokenGenerator->createPlainToken();
        $user->setEmailVerificationToken($this->verificationTokenGenerator->hashToken($plainToken));
        $user->setEmailVerificationTokenExpiresAt($this->verificationTokenGenerator->expiresAt());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        try {
            $this->confirmRegistrationEmailService->send(
                toEmail: $user->getEmail(),
                prenom: $user->getPrenom() ?? '',
                plainToken: $plainToken,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Confirmation email failed after registration.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        }

        return new JsonResponse([
            'message' => 'Votre compte a été créé. Un email de confirmation vous a été envoyé.',
            'requiresEmailVerification' => true,
        ], Response::HTTP_CREATED);
    }

    /**
     * @param array<string,string> $fields
     */
    private function errorResponse(string $message, array $fields, int $status): JsonResponse
    {
        return new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => $message,
            'fields' => $fields,
        ], $status);
    }
}
