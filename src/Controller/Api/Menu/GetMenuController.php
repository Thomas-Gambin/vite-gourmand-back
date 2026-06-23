<?php

declare(strict_types=1);

namespace App\Controller\Api\Menu;

use App\Dto\Menu\MenuDetailDto;
use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class GetMenuController
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
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

        return new JsonResponse(MenuDetailDto::fromMenu($menu)->toArray(), Response::HTTP_OK);
    }
}
