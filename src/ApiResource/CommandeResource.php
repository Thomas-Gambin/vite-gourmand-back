<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Commande\CreateCommandeController;
use App\Controller\Api\Commande\PreviewCommandeController;
use App\Dto\Commande\CreateCommandePayload;
use App\Dto\Commande\CreateCommandePreviewPayload;

#[ApiResource(
    shortName: 'Commande',
    operations: [
        new Post(
            uriTemplate: '/commandes/preview',
            name: 'api_commandes_preview',
            controller: PreviewCommandeController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: CreateCommandePreviewPayload::class,
            output: false,
        ),
        new Post(
            uriTemplate: '/commandes',
            name: 'api_commandes_create',
            controller: CreateCommandeController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: CreateCommandePayload::class,
            output: false,
        ),
    ],
)]
final class CommandeResource
{
}
