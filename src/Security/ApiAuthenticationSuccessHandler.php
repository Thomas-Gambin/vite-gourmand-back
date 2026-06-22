<?php

declare(strict_types=1);

namespace App\Security;

use App\Dto\Auth\UserProfileDto;
use App\Entity\User;
use App\Service\Auth\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class ApiAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenService $refreshTokenService,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'message' => 'Authentification réussie.',
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'message' => 'Authentification réussie.',
            'token' => $this->jwtManager->create($user),
            'refresh_token' => $this->refreshTokenService->generateForUser($user),
            'refresh_token_expiration' => $this->refreshTokenService->getExpirationTimestamp(),
            'user' => UserProfileDto::fromUser($user)->toArray(),
        ], Response::HTTP_OK);
    }
}
