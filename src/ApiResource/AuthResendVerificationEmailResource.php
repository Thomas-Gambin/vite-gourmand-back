<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Auth\ResendVerificationEmailController;
use App\Dto\Auth\ResendVerificationPayload;

#[ApiResource(
    shortName: 'ResendVerificationEmail',
    operations: [
        new Post(
            uriTemplate: '/resend-verification-email',
            name: 'api_resend_verification_email',
            controller: ResendVerificationEmailController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Renvoie un email de confirmation si un compte non vérifié existe.',
            input: ResendVerificationPayload::class,
            output: false,
        ),
    ],
)]
final class AuthResendVerificationEmailResource
{
}
