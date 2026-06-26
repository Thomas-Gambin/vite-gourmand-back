<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\Api\Horaire\ListHorairesController;

#[ApiResource(
    shortName: 'Horaire',
    operations: [
        new GetCollection(
            uriTemplate: '/horaires',
            name: 'api_horaires_list',
            controller: ListHorairesController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            output: false,
        ),
    ],
)]
final class HoraireResource
{
}
