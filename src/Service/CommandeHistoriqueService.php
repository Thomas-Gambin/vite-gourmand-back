<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Commande;
use App\Entity\CommandeHistoriqueStatut;
use Doctrine\ORM\EntityManagerInterface;

final class CommandeHistoriqueService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function record(Commande $commande, string $statut, ?\DateTimeImmutable $date = null): void
    {
        $historique = new CommandeHistoriqueStatut();
        $historique->setStatut($statut);
        $historique->setDateModification($date ?? new \DateTimeImmutable());
        $historique->setCommande($commande);

        $this->entityManager->persist($historique);
    }
}
