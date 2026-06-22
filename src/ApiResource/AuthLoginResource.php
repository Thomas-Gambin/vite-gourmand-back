<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\LoginController;
use App\Dto\Auth\LoginPayload;

/**
 * Documentation API Platform pour POST /api/login.
 * Le traitement réel est intercepté par le firewall Symfony json_login.
 */
#[ApiResource(
    shortName: 'Login',
    operations: [
        new Post(
            uriTemplate: '/login',
            name: 'api_login_doc',
            controller: LoginController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Connexion utilisateur : email et mot de passe. Réponse 200 avec JWT et profil.',
            input: LoginPayload::class,
            output: false,
        ),
    ],
)]
final class AuthLoginResource
{
}
