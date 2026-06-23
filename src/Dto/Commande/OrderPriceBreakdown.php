<?php

declare(strict_types=1);

namespace App\Dto\Commande;

final readonly class OrderPriceBreakdown
{
    public function __construct(
        public string $prixParPersonne,
        public int $nombrePersonne,
        public int $nombrePersonneMinimum,
        public string $sousTotal,
        public string $remise,
        public string $prixMenu,
        public string $prixLivraison,
        public string $total,
        public bool $reductionApplied,
        public string $villePrestation,
        public ?string $distanceLivraisonKm,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'prixParPersonne' => $this->prixParPersonne,
            'nombrePersonne' => $this->nombrePersonne,
            'nombrePersonneMinimum' => $this->nombrePersonneMinimum,
            'sousTotal' => $this->sousTotal,
            'remise' => $this->remise,
            'prixMenu' => $this->prixMenu,
            'prixLivraison' => $this->prixLivraison,
            'total' => $this->total,
            'reductionApplied' => $this->reductionApplied,
            'villePrestation' => $this->villePrestation,
            'distanceLivraisonKm' => $this->distanceLivraisonKm,
        ];
    }
}
