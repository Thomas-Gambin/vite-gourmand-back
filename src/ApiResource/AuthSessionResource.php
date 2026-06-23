<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\LogoutController;

/**
 * Documentation API Platform pour les endpoints de session.
 */
#[ApiResource(
    shortName: 'Session',
    operations: [
        new Post(
            uriTemplate: '/logout',
            name: 'api_logout_doc',
            controller: LogoutController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Révoque le refresh token et déconnecte l\'utilisateur.',
            output: false,
        ),
    ],
)]
final class AuthSessionResource
{
}
