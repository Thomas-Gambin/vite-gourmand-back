<?php

declare(strict_types=1);

namespace App\Dto\Avis;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateReviewPayload
{
    public function __construct(
        #[Assert\NotNull(message: 'La note est obligatoire.')]
        #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.')]
        public readonly int $note,

        #[Assert\NotBlank(message: 'Le commentaire est obligatoire.')]
        public readonly string $commentaire,
    ) {
    }
}
