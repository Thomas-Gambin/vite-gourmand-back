<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use App\Service\CommandeStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateCommandeStatutPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
        #[Assert\Choice(
            choices: [
                CommandeStatus::EN_ATTENTE,
                CommandeStatus::ACCEPTE,
                CommandeStatus::EN_PREPARATION,
                CommandeStatus::EN_COURS_DE_LIVRAISON,
                CommandeStatus::LIVRE,
                CommandeStatus::EN_ATTENTE_RETOUR_MATERIEL,
                CommandeStatus::TERMINEE,
                CommandeStatus::ANNULEE,
            ],
            message: 'Le statut fourni est invalide.',
        )]
        public readonly string $statut,
        #[Assert\Choice(choices: ['gsm', 'email'], message: 'Le mode de contact est invalide.')]
        public readonly ?string $contactMode = null,
        #[Assert\Length(min: 3, minMessage: 'Le motif doit contenir au moins {{ limit }} caractères.')]
        public readonly ?string $employeeActionReason = null,
        public readonly ?\DateTimeImmutable $contactedAt = null,
    ) {
    }
}
