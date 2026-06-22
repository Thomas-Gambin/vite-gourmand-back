<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\ForgotPasswordController;
use App\Dto\Auth\ForgotPasswordPayload;

#[ApiResource(
    shortName: 'ForgotPassword',
    operations: [
        new Post(
            uriTemplate: '/forgot-password',
            name: 'api_forgot_password',
            controller: ForgotPasswordController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Demande de réinitialisation de mot de passe par email.',
            input: ForgotPasswordPayload::class,
            output: false,
        ),
    ],
)]
final class AuthForgotPasswordResource
{
}
