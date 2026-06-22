<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\ResetPasswordController;
use App\Dto\Auth\ResetPasswordPayload;

#[ApiResource(
    shortName: 'ResetPassword',
    operations: [
        new Post(
            uriTemplate: '/reset-password',
            name: 'api_reset_password',
            controller: ResetPasswordController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Réinitialise le mot de passe avec le token reçu par email.',
            input: ResetPasswordPayload::class,
            output: false,
        ),
    ],
)]
final class AuthResetPasswordResource
{
}
