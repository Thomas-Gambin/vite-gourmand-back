<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateCommandePayload
{
    public function __construct(
        #[Assert\NotNull(message: 'Le menu est obligatoire.')]
        #[Assert\Positive(message: 'Le menu est invalide.')]
        public readonly int $menuId,

        #[Assert\NotBlank(message: "L'adresse de prestation est obligatoire.")]
        #[Assert\Length(max: 255)]
        public readonly string $adressePrestation,

        #[Assert\NotBlank(message: 'La ville de prestation est obligatoire.')]
        #[Assert\Length(max: 50)]
        public readonly string $villePrestation,

        #[Assert\Length(max: 10)]
        public readonly ?string $codePostalPrestation = null,

        #[Assert\NotBlank(message: 'La date de prestation est obligatoire.')]
        public readonly string $datePrestation,

        #[Assert\NotBlank(message: "L'heure de livraison est obligatoire.")]
        public readonly string $heureLivraison,

        #[Assert\NotNull(message: 'Le nombre de personnes est obligatoire.')]
        #[Assert\Positive(message: 'Le nombre de personnes doit être strictement positif.')]
        public readonly int $nombrePersonne,

        public readonly bool $pretMateriel = false,
    ) {
    }
}
