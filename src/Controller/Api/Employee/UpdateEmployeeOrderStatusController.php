<?php

declare(strict_types=1);

namespace App\Controller\Api\Employee;

use App\Dto\Commande\CommandeDetailDto;
use App\Dto\Commande\EmployeeContactPayload;
use App\Dto\Commande\UpdateCommandeStatutPayload;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Service\CommandeStatus;
use App\Service\EmployeeCommandeService;
use App\Service\OrderPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class UpdateEmployeeOrderStatusController
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeRepository $commandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EmployeeCommandeService $employeeCommandeService,
        private readonly OrderPriceCalculator $priceCalculator,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(
        '/api/employee/commandes/{id}/statut',
        name: 'api_employee_commandes_statut',
        methods: ['PATCH'],
        requirements: ['id' => '\d+'],
    )]
    public function __invoke(int $id, #[MapRequestPayload] UpdateCommandeStatutPayload $payload): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (
            !$this->security->isGranted('ROLE_EMPLOYEE')
            && !$this->security->isGranted('ROLE_ADMIN')
        ) {
            return new JsonResponse([
                'code' => 'FORBIDDEN',
                'message' => 'Accès réservé aux employés.',
            ], Response::HTTP_FORBIDDEN);
        }

        $violations = $this->validator->validate($payload);
        if (count($violations) > 0) {
            $fields = [];
            foreach ($violations as $violation) {
                $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
            }

            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Les données fournies sont invalides.',
                'fields' => $fields,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commande = $this->commandeRepository->find($id);
        if ($commande === null) {
            return new JsonResponse([
                'code' => 'NOT_FOUND',
                'message' => 'Commande introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($payload->statut === CommandeStatus::ANNULEE) {
            $contactViolations = $this->validator->validate(new EmployeeContactPayload(
                contactMode: $payload->contactMode,
                employeeActionReason: $payload->employeeActionReason,
                contactedAt: $payload->contactedAt,
            ));
            if (count($contactViolations) > 0) {
                $fields = [];
                foreach ($contactViolations as $violation) {
                    $fields[$violation->getPropertyPath()] = (string) $violation->getMessage();
                }

                return new JsonResponse([
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les informations de contact sont obligatoires pour annuler une commande.',
                    'fields' => $fields,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            $this->employeeCommandeService->changerStatut(
                $commande,
                $payload->statut,
                $user,
                $payload->statut === CommandeStatus::ANNULEE
                    ? new EmployeeContactPayload(
                        contactMode: $payload->contactMode,
                        employeeActionReason: $payload->employeeActionReason,
                        contactedAt: $payload->contactedAt,
                    )
                    : null,
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->flush();

        $commande = $this->commandeRepository->find($id);

        return new JsonResponse([
            'message' => 'Statut de la commande mis à jour.',
            'order' => CommandeDetailDto::fromCommande($commande, $this->priceCalculator)->toArray(),
        ], Response::HTTP_OK);
    }
}
