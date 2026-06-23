<?php

declare(strict_types=1);

namespace App\Dto\Avis;

use App\Entity\Commande;

final readonly class AvailableReviewDto
{
    public function __construct(
        public int $commandeId,
        public string $numeroCommande,
        public string $menuTitre,
        public string $datePrestation,
    ) {
    }

    public static function fromCommande(Commande $commande): self
    {
        return new self(
            commandeId: (int) $commande->getId(),
            numeroCommande: (string) $commande->getNumeroCommande(),
            menuTitre: (string) $commande->getMenu()?->getTitre(),
            datePrestation: $commande->getDatePrestation()?->format('Y-m-d') ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'commandeId' => $this->commandeId,
            'numeroCommande' => $this->numeroCommande,
            'menuTitre' => $this->menuTitre,
            'datePrestation' => $this->datePrestation,
        ];
    }
}
