<?php

declare(strict_types=1);

namespace App\Dto\Commande;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateCommandePreviewPayload
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

        #[Assert\NotNull(message: 'Le nombre de personnes est obligatoire.')]
        #[Assert\Positive(message: 'Le nombre de personnes doit être strictement positif.')]
        public readonly int $nombrePersonne,
    ) {
    }
}
