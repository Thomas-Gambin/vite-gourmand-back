<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\MeController;
use App\Controller\Api\Auth\UpdateMeController;
use App\Controller\Api\Me\CancelUserOrderController;
use App\Controller\Api\Me\CreateOrderReviewController;
use App\Controller\Api\Me\GetUserOrderController;
use App\Controller\Api\Me\GetUserOrderTrackingController;
use App\Controller\Api\Me\ListAvailableReviewsController;
use App\Controller\Api\Me\ListUserOrdersController;
use App\Controller\Api\Me\UpdateUserOrderController;
use App\Dto\Auth\UpdateProfilePayload;
use App\Dto\Avis\CreateReviewPayload;
use App\Dto\Commande\UpdateCommandePayload;

#[ApiResource(
    shortName: 'MonCompte',
    operations: [
        new Get(
            uriTemplate: '/me',
            name: 'api_me_doc',
            controller: MeController::class,
            read: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Patch(
            uriTemplate: '/me',
            name: 'api_me_update_doc',
            controller: UpdateMeController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: UpdateProfilePayload::class,
            output: false,
        ),
        new GetCollection(
            uriTemplate: '/me/orders',
            name: 'api_me_orders_list_doc',
            controller: ListUserOrdersController::class,
            read: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Get(
            uriTemplate: '/me/orders/{id}',
            name: 'api_me_orders_get_doc',
            controller: GetUserOrderController::class,
            read: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Patch(
            uriTemplate: '/me/orders/{id}',
            name: 'api_me_orders_update_doc',
            controller: UpdateUserOrderController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: UpdateCommandePayload::class,
            output: false,
        ),
        new Patch(
            uriTemplate: '/me/orders/{id}/cancel',
            name: 'api_me_orders_cancel_doc',
            controller: CancelUserOrderController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Get(
            uriTemplate: '/me/orders/{id}/tracking',
            name: 'api_me_orders_tracking_doc',
            controller: GetUserOrderTrackingController::class,
            read: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new GetCollection(
            uriTemplate: '/me/reviews/available',
            name: 'api_me_reviews_available_doc',
            controller: ListAvailableReviewsController::class,
            read: false,
            write: false,
            serialize: false,
            output: false,
        ),
        new Post(
            uriTemplate: '/me/orders/{id}/review',
            name: 'api_me_orders_review_doc',
            controller: CreateOrderReviewController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: CreateReviewPayload::class,
            output: false,
        ),
    ],
)]
final class MeResource
{
}
