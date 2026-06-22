<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

final class ApiAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        for ($e = $exception; $e !== null; $e = $e->getPrevious()) {
            if ($e instanceof CustomUserMessageAccountStatusException) {
                return new JsonResponse([
                    'code' => 'EMAIL_NOT_VERIFIED',
                    'message' => $e->getMessage(),
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return new JsonResponse([
            'code' => 'INVALID_CREDENTIALS',
            'message' => 'Identifiants invalides.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
