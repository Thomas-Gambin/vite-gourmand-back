<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Réponses JSON 401/403 pour les routes /api/* (évite les redirections HTML).
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 8)]
final class ApiSecurityExceptionListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();
        if (!$throwable instanceof AccessDeniedException) {
            return;
        }

        if ($this->isUnauthenticated($throwable)) {
            $event->setResponse(new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED));

            return;
        }

        $event->setResponse(
            new JsonResponse(
                [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé.',
                ],
                Response::HTTP_FORBIDDEN,
            ),
        );
    }

    private function isUnauthenticated(AccessDeniedException $exception): bool
    {
        $previous = $exception->getPrevious();
        if ($previous instanceof AuthenticationCredentialsNotFoundException
            || $previous instanceof InsufficientAuthenticationException
        ) {
            return true;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return true;
        }

        $user = $token->getUser();

        return !$user instanceof UserInterface;
    }
}
