<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\UpdateProfilePayload;
use App\Dto\Auth\UserProfileDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class UpdateMeController
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/me', name: 'api_me_update', methods: ['PATCH'])]
    public function __invoke(#[MapRequestPayload] UpdateProfilePayload $payload): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $violations = $this->validator->validate($payload);
        if (count($violations) > 0) {
            return $this->validationErrorResponse($violations);
        }

        $email = strtolower(trim($payload->email));
        if ($email !== $user->getEmail()) {
            $existing = $this->userRepository->findOneBy(['email' => $email]);
            if ($existing !== null && $existing->getId() !== $user->getId()) {
                return new JsonResponse([
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les informations du profil sont invalides.',
                    'fields' => ['email' => 'Cet email est déjà utilisé.'],
                ], Response::HTTP_BAD_REQUEST);
            }
            $user->setEmail($email);
        }

        $user->setNom(trim($payload->nom));
        $user->setPrenom(trim($payload->prenom));
        $user->setTelephone(trim($payload->telephone));
        $user->setVille(trim($payload->ville));
        $user->setPays(trim($payload->pays));
        $user->setAdressePostale(trim($payload->adressePostale));

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Profil mis à jour avec succès.',
            'user' => UserProfileDto::fromUser($user)->toArray(),
        ], Response::HTTP_OK);
    }

    private function validationErrorResponse(iterable $violations): JsonResponse
    {
        $fields = [];
        foreach ($violations as $violation) {
            $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }

        return new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => 'Les informations du profil sont invalides.',
            'fields' => $fields,
        ], Response::HTTP_BAD_REQUEST);
    }
}
