<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\VerifyEmailController;
use App\Dto\Auth\VerifyEmailPayload;

#[ApiResource(
    shortName: 'VerifyEmail',
    operations: [
        new Post(
            uriTemplate: '/verify-email',
            name: 'api_verify_email',
            controller: VerifyEmailController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Confirme l’adresse email à partir du token reçu par email.',
            input: VerifyEmailPayload::class,
            output: false,
        ),
    ],
)]
final class AuthVerifyEmailResource
{
}
