<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\UserProfileDto;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class MeController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => UserProfileDto::fromUser($user)->toArray(),
        ], Response::HTTP_OK);
    }
}
