<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\RegisterController;
use App\Dto\Auth\RegisterPayload;

/**
 * Point d’entrée documenté API Platform pour l’inscription (POST /api/register).
 * Le traitement reste délégué à {@see RegisterController}.
 */
#[ApiResource(
    shortName: 'Register',
    operations: [
        new Post(
            uriTemplate: '/register',
            name: 'api_register',
            controller: RegisterController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Inscription : nom, prénom, email, mot de passe et coordonnées. Réponse 201 ou 409 si email déjà utilisé.',
            input: RegisterPayload::class,
            output: false,
        ),
    ],
)]
final class AuthRegisterResource
{
}
