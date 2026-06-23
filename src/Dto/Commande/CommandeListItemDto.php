<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use App\Entity\Commande;
use App\Service\CommandeStatus;
use App\Service\MoneyMath;
use App\Service\OrderPriceCalculator;

final readonly class CommandeListItemDto
{
    public function __construct(
        public int $id,
        public string $numeroCommande,
        public int $menuId,
        public string $menuTitre,
        public string $datePrestation,
        public string $heureLivraison,
        public string $adressePrestation,
        public string $villePrestation,
        public ?string $codePostalPrestation,
        public int $nombrePersonne,
        public string $prixMenu,
        public string $prixLivraison,
        public string $remise,
        public string $total,
        public string $statut,
        public string $dateCommande,
        public bool $canEdit,
        public bool $canCancel,
        public bool $canTrack,
        public bool $canReview,
    ) {
    }

    public static function fromCommande(Commande $commande, OrderPriceCalculator $calculator): self
    {
        $menu = $commande->getMenu();
        $breakdown = $calculator->calculate(
            $menu,
            (int) $commande->getNombrePersonne(),
            (string) $commande->getAdressePrestation(),
            (string) $commande->getVillePrestation(),
            $commande->getCodePostalPrestation(),
        );
        $total = MoneyMath::add((string) $commande->getPrixMenu(), (string) $commande->getPrixLivraison());
        $statut = (string) $commande->getStatut();

        return new self(
            id: (int) $commande->getId(),
            numeroCommande: (string) $commande->getNumeroCommande(),
            menuId: (int) $menu?->getId(),
            menuTitre: (string) $menu?->getTitre(),
            datePrestation: $commande->getDatePrestation()?->format('Y-m-d') ?? '',
            heureLivraison: (string) $commande->getHeureLivraison(),
            adressePrestation: (string) $commande->getAdressePrestation(),
            villePrestation: (string) $commande->getVillePrestation(),
            codePostalPrestation: $commande->getCodePostalPrestation(),
            nombrePersonne: (int) $commande->getNombrePersonne(),
            prixMenu: (string) $commande->getPrixMenu(),
            prixLivraison: (string) $commande->getPrixLivraison(),
            remise: $breakdown->remise,
            total: $total,
            statut: $statut,
            dateCommande: $commande->getDateCommande()?->format(\DateTimeInterface::ATOM) ?? '',
            canEdit: CommandeStatus::isEditable($statut),
            canCancel: CommandeStatus::isCancellable($statut),
            canTrack: CommandeStatus::isTrackable($statut),
            canReview: CommandeStatus::isReviewable($statut) && $commande->getAvis() === null,
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
            'menuId' => $this->menuId,
            'menuTitre' => $this->menuTitre,
            'datePrestation' => $this->datePrestation,
            'heureLivraison' => $this->heureLivraison,
            'adressePrestation' => $this->adressePrestation,
            'villePrestation' => $this->villePrestation,
            'codePostalPrestation' => $this->codePostalPrestation,
            'nombrePersonne' => $this->nombrePersonne,
            'prixMenu' => $this->prixMenu,
            'prixLivraison' => $this->prixLivraison,
            'remise' => $this->remise,
            'total' => $this->total,
            'statut' => $this->statut,
            'dateCommande' => $this->dateCommande,
            'canEdit' => $this->canEdit,
            'canCancel' => $this->canCancel,
            'canTrack' => $this->canTrack,
            'canReview' => $this->canReview,
        ];
    }
}
