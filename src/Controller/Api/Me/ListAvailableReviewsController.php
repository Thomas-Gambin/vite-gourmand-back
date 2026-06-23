<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Avis\AvailableReviewDto;
use App\Entity\User;
use App\Repository\CommandeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ListAvailableReviewsController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
    ) {
    }

    #[Route('/api/me/reviews/available', name: 'api_me_reviews_available', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $commandes = $this->commandeRepository->findReviewableByUtilisateur($user);
        $items = array_map(
            fn ($commande) => AvailableReviewDto::fromCommande($commande)->toArray(),
            $commandes
        );

        return new JsonResponse([
            'reviews' => $items,
        ], Response::HTTP_OK);
    }
}
