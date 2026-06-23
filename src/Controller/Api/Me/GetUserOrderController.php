<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Commande\CommandeDetailDto;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Service\OrderPriceCalculator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetUserOrderController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly OrderPriceCalculator $priceCalculator,
    ) {
    }

    #[Route('/api/me/orders/{id}', name: 'api_me_orders_get', methods: ['GET'], requirements: ['id' => '\d+'])]
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

        return new JsonResponse([
            'order' => CommandeDetailDto::fromCommande($commande, $this->priceCalculator)->toArray(),
        ], Response::HTTP_OK);
    }
}
