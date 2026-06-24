<?php

declare(strict_types=1);

namespace App\Controller\Api\Me;

use App\Dto\Avis\CreateReviewPayload;
use App\Entity\Avis;
use App\Entity\User;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Service\AvisStatus;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class CreateOrderReviewController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly AvisRepository $avisRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me/orders/{id}/review', name: 'api_me_orders_review', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function __invoke(int $id, #[MapRequestPayload] CreateReviewPayload $payload): JsonResponse
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

        if (!CommandeStatus::isReviewable((string) $commande->getStatut())) {
            return new JsonResponse([
                'code' => 'FORBIDDEN',
                'message' => 'Un avis ne peut être déposé que pour une commande terminée.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($this->avisRepository->findOneByCommande($commande) !== null) {
            return new JsonResponse([
                'code' => 'CONFLICT',
                'message' => 'Un avis existe déjà pour cette commande.',
            ], Response::HTTP_CONFLICT);
        }

        $violations = $this->validator->validate($payload);
        if (count($violations) > 0) {
            $fields = [];
            foreach ($violations as $violation) {
                $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
            }

            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => "L'avis est invalide.",
                'fields' => $fields,
            ], Response::HTTP_BAD_REQUEST);
        }

        $avis = new Avis();
        $avis->setNote($payload->note);
        $avis->setDescription(trim($payload->commentaire));
        $avis->setStatut(AvisStatus::EN_ATTENTE);
        $avis->setUtilisateur($user);
        $avis->setCommande($commande);

        $this->entityManager->persist($avis);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Votre avis a été envoyé et sera publié après validation.',
            'review' => [
                'id' => $avis->getId(),
                'note' => $avis->getNote(),
                'statut' => $avis->getStatut(),
                'commandeId' => $commande->getId(),
            ],
        ], Response::HTTP_CREATED);
    }
}
