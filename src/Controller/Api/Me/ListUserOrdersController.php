<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Commande\CommandeListItemDto;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Service\OrderPriceCalculator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ListUserOrdersController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly OrderPriceCalculator $priceCalculator,
    ) {
    }

    #[Route('/api/me/orders', name: 'api_me_orders_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $commandes = $this->commandeRepository->findByUtilisateur($user);
        $items = array_map(
            fn ($commande) => CommandeListItemDto::fromCommande($commande, $this->priceCalculator)->toArray(),
            $commandes
        );

        return new JsonResponse([
            'orders' => $items,
        ], Response::HTTP_OK);
    }
}
