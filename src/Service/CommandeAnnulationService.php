<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Commande\EmployeeContactPayload;
use App\Entity\Commande;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final class CommandeAnnulationService
{
    public function __construct(
        private readonly CommandeStatutService $statutService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function annuler(
        Commande $commande,
        User $auteur,
        ?EmployeeContactPayload $contact = null,
        bool $requireContact = true,
    ): void {
        if ($requireContact) {
            $this->assertContactProvided($contact);
            $commande->setContactMode($contact->contactMode);
            $commande->setEmployeeActionReason($contact->employeeActionReason);
            $commande->setContactedAt($contact->contactedAt);
        }

        $this->statutService->changerStatut($commande, CommandeStatus::ANNULEE, $auteur);
        $this->restaurerStock($commande);
    }

    public function restaurerStock(Commande $commande): void
    {
        $menu = $commande->getMenu();
        if ($menu !== null) {
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + 1);
        }
    }

    private function assertContactProvided(?EmployeeContactPayload $contact): void
    {
        if ($contact === null) {
            throw new InvalidArgumentException('Les informations de contact client sont obligatoires pour annuler une commande.');
        }

        if ($contact->contactMode === null || $contact->contactMode === '') {
            throw new InvalidArgumentException('Le mode de contact est obligatoire.');
        }

        if ($contact->employeeActionReason === null || trim($contact->employeeActionReason) === '') {
            throw new InvalidArgumentException('Le motif est obligatoire.');
        }

        if ($contact->contactedAt === null) {
            throw new InvalidArgumentException('La date de contact est obligatoire.');
        }
    }
}
