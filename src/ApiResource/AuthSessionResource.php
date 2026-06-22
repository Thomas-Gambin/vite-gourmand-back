<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\LogoutController;
use App\Controller\Api\Auth\MeController;

/**
 * Documentation API Platform pour les endpoints de session.
 */
#[ApiResource(
    shortName: 'Session',
    operations: [
        new Get(
            uriTemplate: '/me',
            name: 'api_me_doc',
            controller: MeController::class,
            read: false,
            write: false,
            serialize: false,
            description: 'Retourne le profil de l’utilisateur connecté.',
            output: false,
        ),
        new Post(
            uriTemplate: '/logout',
            name: 'api_logout_doc',
            controller: LogoutController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Révoque le refresh token et déconnecte l’utilisateur.',
            output: false,
        ),
    ],
)]
final class AuthSessionResource
{
}
