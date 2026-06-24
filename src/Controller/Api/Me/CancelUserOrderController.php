<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Commande\CommandeDetailDto;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Service\CommandeAnnulationService;
use App\Service\CommandeStatus;
use App\Service\OrderPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CancelUserOrderController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommandeAnnulationService $annulationService,
        private readonly OrderPriceCalculator $priceCalculator,
    ) {
    }

    #[Route('/api/me/orders/{id}/cancel', name: 'api_me_orders_cancel', methods: ['PATCH'], requirements: ['id' => '\d+'])]
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

        if (!CommandeStatus::isCancellable((string) $commande->getStatut())) {
            return new JsonResponse([
                'code' => 'FORBIDDEN',
                'message' => 'Cette commande ne peut plus être annulée.',
            ], Response::HTTP_FORBIDDEN);
        }

        $this->annulationService->annuler($commande, $user, null, false);
        $this->entityManager->flush();

        $commande = $this->commandeRepository->findOneByIdAndUtilisateur($id, $user);

        return new JsonResponse([
            'message' => 'Commande annulée avec succès.',
            'order' => CommandeDetailDto::fromCommande($commande, $this->priceCalculator)->toArray(),
        ], Response::HTTP_OK);
    }
}
