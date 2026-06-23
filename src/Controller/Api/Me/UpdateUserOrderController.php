<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Commande\CommandeDetailDto;
use App\Dto\Commande\UpdateCommandePayload;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Service\CommandeStatus;
use App\Service\CommandeValidator;
use App\Service\OrderPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class UpdateUserOrderController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderPriceCalculator $priceCalculator,
        private readonly CommandeValidator $commandeValidator,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me/orders/{id}', name: 'api_me_orders_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function __invoke(int $id, #[MapRequestPayload] UpdateCommandePayload $payload): JsonResponse
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

        if (!CommandeStatus::isEditable((string) $commande->getStatut())) {
            return new JsonResponse([
                'code' => 'FORBIDDEN',
                'message' => 'Cette commande ne peut plus être modifiée.',
            ], Response::HTTP_FORBIDDEN);
        }

        $violations = $this->validator->validate($payload);
        if (count($violations) > 0) {
            return $this->validationErrorResponse($violations);
        }

        $menu = $commande->getMenu();
        $fields = $this->commandeValidator->validateForOrder(
            $menu,
            $payload->nombrePersonne,
            $payload->datePrestation,
            $payload->heureLivraison
        );
        if ($fields !== []) {
            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => 'La commande est invalide.',
                'fields' => $fields,
            ], Response::HTTP_BAD_REQUEST);
        }

        $breakdown = $this->priceCalculator->calculate(
            $menu,
            $payload->nombrePersonne,
            $payload->villePrestation
        );

        $commande->setAdressePrestation(trim($payload->adressePrestation));
        $commande->setVillePrestation(trim($payload->villePrestation));
        $commande->setCodePostalPrestation(
            $payload->codePostalPrestation !== null ? trim($payload->codePostalPrestation) : null
        );
        $commande->setDatePrestation(new \DateTimeImmutable($payload->datePrestation));
        $commande->setHeureLivraison($payload->heureLivraison);
        $commande->setNombrePersonne($payload->nombrePersonne);
        $commande->setPretMateriel($payload->pretMateriel);
        $commande->setPrixMenu($breakdown->prixMenu);
        $commande->setPrixLivraison($breakdown->prixLivraison);

        $this->entityManager->flush();

        $commande = $this->commandeRepository->findOneByIdAndUtilisateur($id, $user);

        return new JsonResponse([
            'message' => 'Commande mise à jour avec succès.',
            'order' => CommandeDetailDto::fromCommande($commande, $this->priceCalculator)->toArray(),
        ], Response::HTTP_OK);
    }

    private function validationErrorResponse(iterable $violations): JsonResponse
    {
        $fields = [];
        foreach ($violations as $violation) {
            $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }

        return new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => 'La commande est invalide.',
            'fields' => $fields,
        ], Response::HTTP_BAD_REQUEST);
    }
}
