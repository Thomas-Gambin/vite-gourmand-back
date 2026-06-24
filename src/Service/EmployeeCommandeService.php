<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Commande\EmployeeContactPayload;
use App\Entity\Commande;
use App\Entity\User;
use InvalidArgumentException;

final class EmployeeCommandeService
{
    public function __construct(
        private readonly CommandeStatutService $statutService,
        private readonly CommandeAnnulationService $annulationService,
    ) {
    }

    public function changerStatut(
        Commande $commande,
        string $statut,
        User $auteur,
        ?EmployeeContactPayload $contact = null,
    ): void {
        if (!in_array($statut, CommandeStatus::employeeUpdatableStatuses(), true)) {
            throw new InvalidArgumentException(sprintf('Statut non autorisé pour un employé : %s', $statut));
        }

        if ($statut === CommandeStatus::ANNULEE) {
            $this->annulationService->annuler($commande, $auteur, $contact, true);

            return;
        }

        $this->statutService->changerStatut($commande, $statut, $auteur);
    }
}
