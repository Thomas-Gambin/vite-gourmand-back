<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Commande\CommandeTrackingDto;
use App\Entity\User;
use App\Repository\CommandeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetUserOrderTrackingController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
    ) {
    }

    #[Route('/api/me/orders/{id}/tracking', name: 'api_me_orders_tracking', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function __invoke(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $commande = $this->commandeRepository->findOneByIdAndUtilisateur($id, $user);
        if ($commande === null) {
            return new JsonResponse([
                'code' => 'NOT_FOUND',
                'message' => 'Commande introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!CommandeTrackingDto::isAccessible($commande)) {
            return new JsonResponse([
                'code' => 'FORBIDDEN',
                'message' => 'Le suivi n’est pas encore disponible pour cette commande.',
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'tracking' => CommandeTrackingDto::fromCommande($commande)->toArray(),
        ], Response::HTTP_OK);
    }
}
