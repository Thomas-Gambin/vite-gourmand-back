<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Commande;
use App\Entity\User;
use InvalidArgumentException;

final class CommandeStatutService
{
    public function __construct(
        private readonly CommandeHistoriqueService $historiqueService,
    ) {
    }

    public function changerStatut(
        Commande $commande,
        string $nouveauStatut,
        ?User $auteur = null,
        ?\DateTimeImmutable $date = null,
    ): void {
        if (!CommandeStatus::isValid($nouveauStatut)) {
            throw new InvalidArgumentException(sprintf('Statut de commande invalide : %s', $nouveauStatut));
        }

        $ancienStatut = (string) $commande->getStatut();
        if ($ancienStatut === $nouveauStatut) {
            return;
        }

        $baseDate = $date ?? new \DateTimeImmutable();
        $statutsToRecord = CommandeStatus::getStatutsToRecordOnTransition($ancienStatut, $nouveauStatut);

        foreach ($statutsToRecord as $offset => $statut) {
            $stepDate = $baseDate->modify(sprintf('+%d seconds', $offset));
            $this->historiqueService->record($commande, $statut, $stepDate);
        }

        $commande->setStatut($nouveauStatut);
    }
}
