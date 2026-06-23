<?php

declare(strict_types=1);

namespace App\Controller\Api\Commande;

use App\Dto\Commande\CreateCommandePreviewPayload;
use App\Exception\GeocodingException;
use App\Repository\MenuRepository;
use App\Service\CommandeValidator;
use App\Service\OrderPriceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class PreviewCommandeController
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
        private readonly OrderPriceCalculator $priceCalculator,
        private readonly CommandeValidator $commandeValidator,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateCommandePreviewPayload $payload): JsonResponse
    {
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

        $fields = $this->commandeValidator->validateForPreview(
            $menu,
            $payload->nombrePersonne,
            $payload->adressePrestation,
            $payload->villePrestation,
        );
        if ($fields !== []) {
            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => 'La prévisualisation de la commande est invalide.',
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

        return new JsonResponse($breakdown->toArray(), Response::HTTP_OK);
    }

    private function validationErrorResponse(iterable $violations): JsonResponse
    {
        $fields = [];
        foreach ($violations as $violation) {
            $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }

        return new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => 'La prévisualisation de la commande est invalide.',
            'fields' => $fields,
        ], Response::HTTP_BAD_REQUEST);
    }
}
