<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use App\Entity\Commande;

final readonly class CommandeCreatedDto
{
    public function __construct(
        public int $id,
        public string $numeroCommande,
        public string $statut,
        public string $prixMenu,
        public string $prixLivraison,
        public string $remise,
        public string $total,
        public string $datePrestation,
        public string $heureLivraison,
    ) {
    }

    public static function fromCommande(Commande $commande, OrderPriceBreakdown $breakdown): self
    {
        return new self(
            id: (int) $commande->getId(),
            numeroCommande: (string) $commande->getNumeroCommande(),
            statut: (string) $commande->getStatut(),
            prixMenu: $breakdown->prixMenu,
            prixLivraison: $breakdown->prixLivraison,
            remise: $breakdown->remise,
            total: $breakdown->total,
            datePrestation: $commande->getDatePrestation()?->format('Y-m-d') ?? '',
            heureLivraison: (string) $commande->getHeureLivraison(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'numeroCommande' => $this->numeroCommande,
            'statut' => $this->statut,
            'prixMenu' => $this->prixMenu,
            'prixLivraison' => $this->prixLivraison,
            'remise' => $this->remise,
            'total' => $this->total,
            'datePrestation' => $this->datePrestation,
            'heureLivraison' => $this->heureLivraison,
        ];
    }
}
