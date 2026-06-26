<?php

declare(strict_types=1);

namespace App\Controller\Api\Menu;

use App\Dto\Menu\MenuDetailDto;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ListMenusController
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $menus = $this->menuRepository->findAllForApi();
        $items = array_map(
            static fn ($menu) => MenuDetailDto::fromMenu($menu)->toArray(),
            $menus
        );

        return new JsonResponse($items, Response::HTTP_OK);
    }
}
