<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\UserProfileDto;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class RefreshTokenController
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !isset($payload['refresh_token']) || !\is_string($payload['refresh_token'])) {
            return new JsonResponse([
                'code' => 'MISSING_REFRESH_TOKEN',
                'message' => 'Refresh token manquant.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $refreshToken = $this->refreshTokenRepository->findOneBy(['refreshToken' => $payload['refresh_token']]);
        if (!$refreshToken instanceof RefreshToken || $refreshToken->getValid() < new \DateTime()) {
            return new JsonResponse([
                'code' => 'INVALID_REFRESH_TOKEN',
                'message' => 'Refresh token invalide ou expiré.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->findOneBy(['email' => $refreshToken->getUsername()]);
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'INVALID_REFRESH_TOKEN',
                'message' => 'Refresh token invalide ou expiré.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'token' => $this->jwtManager->create($user),
            'refresh_token' => $refreshToken->getRefreshToken(),
            'refresh_token_expiration' => $refreshToken->getValid()->getTimestamp(),
            'user' => UserProfileDto::fromUser($user)->toArray(),
        ], Response::HTTP_OK);
    }
}
