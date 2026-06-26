<?php

declare(strict_types=1);

namespace App\Controller\Api\Horaire;

use App\Dto\Horaire\HoraireDto;
use App\Repository\HoraireRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ListHorairesController
{
    public function __construct(
        private readonly HoraireRepository $horaireRepository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $items = array_map(
            static fn ($horaire) => HoraireDto::fromEntity($horaire)->toArray(),
            $this->horaireRepository->findAllOrderedByWeekDay(),
        );

        return new JsonResponse($items, Response::HTTP_OK);
    }
}
