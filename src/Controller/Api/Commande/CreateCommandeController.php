<?php

declare(strict_types=1);

namespace App\Controller\Api\Commande;

use App\Dto\Commande\CommandeCreatedDto;
use App\Dto\Commande\CreateCommandePayload;
use App\Entity\Commande;
use App\Entity\User;
use App\Exception\GeocodingException;
use App\Repository\MenuRepository;
use App\Service\CommandeNumberGenerator;
use App\Service\CommandeStatutService;
use App\Service\CommandeStatus;
use App\Service\CommandeValidator;
use App\Service\Mail\OrderConfirmationEmailService;
use App\Service\OrderPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class CreateCommandeController
{
    public function __construct(
        private readonly Security $security,
        private readonly MenuRepository $menuRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderPriceCalculator $priceCalculator,
        private readonly CommandeValidator $commandeValidator,
        private readonly CommandeNumberGenerator $numberGenerator,
        private readonly CommandeStatutService $statutService,
        private readonly OrderConfirmationEmailService $confirmationEmailService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateCommandePayload $payload): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $violations = $this->validator->validate($payload);
        if (count($violations) > 0) {
            return $this->validationErrorResponse($violations);
        }

        $menu = $this->menuRepository->find($payload->menuId);
        if ($menu === null) {
            return new JsonResponse([
                'code' => 'NOT_FOUND',
                'message' => 'Menu introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

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

        try {
            $breakdown = $this->priceCalculator->calculate(
                $menu,
                $payload->nombrePersonne,
                $payload->adressePrestation,
                $payload->villePrestation,
                $payload->codePostalPrestation,
            );
        } catch (GeocodingException) {
            return new JsonResponse([
                'code' => 'GEOCODING_FAILED',
                'message' => 'Impossible de localiser cette adresse. Vérifiez l\'adresse, le code postal et la ville.',
                'fields' => [
                    'adressePrestation' => 'Impossible de localiser cette adresse. Vérifiez l\'adresse, le code postal et la ville.',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $commande = new Commande();
        $commande->setNumeroCommande($this->numberGenerator->generate());
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setDatePrestation(new \DateTimeImmutable($payload->datePrestation));
        $commande->setHeureLivraison($payload->heureLivraison);
        $commande->setNombrePersonne($payload->nombrePersonne);
        $commande->setPrixMenu($breakdown->prixMenu);
        $commande->setPrixLivraison($breakdown->prixLivraison);
        $commande->setDistanceLivraisonKm($breakdown->distanceLivraisonKm);
        $commande->setPretMateriel($payload->pretMateriel);
        $commande->setRestitutionMateriel(false);
        $commande->setAdressePrestation(trim($payload->adressePrestation));
        $commande->setVillePrestation(trim($payload->villePrestation));
        $commande->setCodePostalPrestation(
            $payload->codePostalPrestation !== null ? trim($payload->codePostalPrestation) : null
        );
        $commande->setUtilisateur($user);
        $commande->setMenu($menu);

        $this->statutService->changerStatut($commande, CommandeStatus::EN_ATTENTE, $user, $commande->getDateCommande());

        $menu->setQuantiteRestante($menu->getQuantiteRestante() - 1);

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        try {
            $this->confirmationEmailService->send(
                toEmail: (string) $user->getEmail(),
                prenom: (string) $user->getPrenom(),
                commande: $commande,
                menuTitle: (string) $menu->getTitre(),
                total: $breakdown->total,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Order confirmation email failed.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'commandeId' => $commande->getId(),
                'userId' => $user->getId(),
            ]);
        }

        return new JsonResponse([
            'message' => 'Commande créée avec succès.',
            'commande' => CommandeCreatedDto::fromCommande($commande, $breakdown)->toArray(),
        ], Response::HTTP_CREATED);
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
