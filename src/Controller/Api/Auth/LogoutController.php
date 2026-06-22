<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class LogoutController
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $refreshTokenValue = is_array($payload) ? ($payload['refresh_token'] ?? null) : null;

        if (is_string($refreshTokenValue) && '' !== trim($refreshTokenValue)) {
            $refreshToken = $this->refreshTokenRepository->findOneBy(['refreshToken' => $refreshTokenValue]);
            if (null !== $refreshToken) {
                $this->entityManager->remove($refreshToken);
                $this->entityManager->flush();
            }
        }

        return new JsonResponse([
            'message' => 'Déconnexion réussie.',
        ], Response::HTTP_OK);
    }
}
