<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use App\Entity\Commande;
use App\Service\CommandeStatus;
use App\Service\MoneyMath;
use App\Service\OrderPriceCalculator;

final readonly class CommandeDetailDto
{
    /**
     * @param array<string, mixed> $client
     * @param array<string, mixed> $menu
     * @param list<array<string, string>> $historique
     */
    public function __construct(
        public int $id,
        public string $numeroCommande,
        public string $statut,
        public string $dateCommande,
        public string $datePrestation,
        public string $heureLivraison,
        public int $nombrePersonne,
        public string $adressePrestation,
        public string $villePrestation,
        public ?string $codePostalPrestation,
        public bool $pretMateriel,
        public string $prixMenu,
        public string $prixLivraison,
        public string $remise,
        public string $total,
        public array $client,
        public array $menu,
        public array $historique,
        public bool $canEdit,
        public bool $canCancel,
        public bool $canTrack,
        public bool $canReview,
    ) {
    }

    public static function fromCommande(Commande $commande, OrderPriceCalculator $calculator): self
    {
        $menu = $commande->getMenu();
        $user = $commande->getUtilisateur();
        $breakdown = $calculator->calculate(
            $menu,
            (int) $commande->getNombrePersonne(),
            (string) $commande->getAdressePrestation(),
            (string) $commande->getVillePrestation(),
            $commande->getCodePostalPrestation(),
        );
        $total = MoneyMath::add((string) $commande->getPrixMenu(), (string) $commande->getPrixLivraison());
        $statut = (string) $commande->getStatut();

        $historique = [];
        foreach ($commande->getHistoriqueStatuts() as $entry) {
            $historique[] = [
                'statut' => (string) $entry->getStatut(),
                'dateModification' => $entry->getDateModification()?->format(\DateTimeInterface::ATOM) ?? '',
            ];
        }

        return new self(
            id: (int) $commande->getId(),
            numeroCommande: (string) $commande->getNumeroCommande(),
            statut: $statut,
            dateCommande: $commande->getDateCommande()?->format(\DateTimeInterface::ATOM) ?? '',
            datePrestation: $commande->getDatePrestation()?->format('Y-m-d') ?? '',
            heureLivraison: (string) $commande->getHeureLivraison(),
            nombrePersonne: (int) $commande->getNombrePersonne(),
            adressePrestation: (string) $commande->getAdressePrestation(),
            villePrestation: (string) $commande->getVillePrestation(),
            codePostalPrestation: $commande->getCodePostalPrestation(),
            pretMateriel: $commande->isPretMateriel(),
            prixMenu: (string) $commande->getPrixMenu(),
            prixLivraison: (string) $commande->getPrixLivraison(),
            remise: $breakdown->remise,
            total: $total,
            client: [
                'prenom' => (string) $user?->getPrenom(),
                'nom' => (string) $user?->getNom(),
                'email' => (string) $user?->getEmail(),
                'telephone' => (string) $user?->getTelephone(),
            ],
            menu: [
                'id' => (int) $menu?->getId(),
                'titre' => (string) $menu?->getTitre(),
            ],
            historique: $historique,
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
            'statut' => $this->statut,
            'dateCommande' => $this->dateCommande,
            'datePrestation' => $this->datePrestation,
            'heureLivraison' => $this->heureLivraison,
            'nombrePersonne' => $this->nombrePersonne,
            'adressePrestation' => $this->adressePrestation,
            'villePrestation' => $this->villePrestation,
            'codePostalPrestation' => $this->codePostalPrestation,
            'pretMateriel' => $this->pretMateriel,
            'prixMenu' => $this->prixMenu,
            'prixLivraison' => $this->prixLivraison,
            'remise' => $this->remise,
            'total' => $this->total,
            'client' => $this->client,
            'menu' => $this->menu,
            'historique' => $this->historique,
            'canEdit' => $this->canEdit,
            'canCancel' => $this->canCancel,
            'canTrack' => $this->canTrack,
            'canReview' => $this->canReview,
        ];
    }
}
