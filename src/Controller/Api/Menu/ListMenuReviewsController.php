<?php

declare(strict_types=1);

namespace App\Controller\Api\Menu;

use App\Dto\Avis\PublicReviewDto;
use App\Repository\AvisRepository;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ListMenuReviewsController
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
        private readonly AvisRepository $avisRepository,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        $menu = $this->menuRepository->find($id);
        if ($menu === null) {
            return new JsonResponse([
                'code' => 'NOT_FOUND',
                'message' => 'Menu introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

        $avisList = $this->avisRepository->findValidatedByMenuId($id);
        $reviews = array_map(
            fn ($avis) => PublicReviewDto::fromAvis($avis)->toArray(),
            $avisList,
        );

        $totalCount = count($reviews);
        $averageRating = null;
        if ($totalCount > 0) {
            $sum = array_sum(array_column($reviews, 'rating'));
            $averageRating = round($sum / $totalCount, 1);
        }

        return new JsonResponse([
            'reviews' => $reviews,
            'totalCount' => $totalCount,
            'averageRating' => $averageRating,
        ], Response::HTTP_OK);
    }
}
