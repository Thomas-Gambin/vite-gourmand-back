<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use App\Entity\Commande;
use App\Service\CommandeStatus;

final readonly class CommandeTrackingDto
{
    /**
     * @param list<array<string, string>> $etapes
     */
    public function __construct(
        public int $id,
        public string $numeroCommande,
        public string $statutActuel,
        public array $etapes,
    ) {
    }

    public static function fromCommande(Commande $commande): self
    {
        $etapes = [];
        foreach ($commande->getHistoriqueStatuts() as $entry) {
            $date = $entry->getDateModification();
            $etapes[] = [
                'id' => (int) $entry->getId(),
                'statut' => (string) $entry->getStatut(),
                'dateModification' => $date?->format(\DateTimeInterface::ATOM) ?? '',
                'heureModification' => $date?->format('H:i') ?? '',
            ];
        }

        return new self(
            id: (int) $commande->getId(),
            numeroCommande: (string) $commande->getNumeroCommande(),
            statutActuel: (string) $commande->getStatut(),
            etapes: $etapes,
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
            'statutActuel' => $this->statutActuel,
            'etapes' => $this->etapes,
        ];
    }

    public static function isAccessible(Commande $commande): bool
    {
        return CommandeStatus::isTrackable((string) $commande->getStatut());
    }
}
