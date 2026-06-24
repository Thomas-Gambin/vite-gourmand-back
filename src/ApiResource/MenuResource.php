<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\Api\Menu\GetMenuController;
use App\Controller\Api\Menu\ListMenuReviewsController;
use App\Controller\Api\Menu\ListMenusController;

#[ApiResource(
    shortName: 'Menu',
    operations: [
        new GetCollection(
            uriTemplate: '/menus',
            name: 'api_menus_list',
            controller: ListMenusController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Get(
            uriTemplate: '/menus/{id}',
            name: 'api_menus_get',
            controller: GetMenuController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Get(
            uriTemplate: '/menus/{id}/reviews',
            name: 'api_menus_reviews',
            controller: ListMenuReviewsController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            output: false,
        ),
    ],
)]
final class MenuResource
{
}
