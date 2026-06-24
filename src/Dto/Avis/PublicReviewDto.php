<?php

declare(strict_types=1);

namespace App\Dto\Avis;

use App\Entity\Avis;
use App\Entity\User;

final readonly class PublicReviewDto
{
    public function __construct(
        public int $id,
        public int $rating,
        public string $comment,
        public string $author,
    ) {
    }

    public static function fromAvis(Avis $avis): self
    {
        return new self(
            id: (int) $avis->getId(),
            rating: (int) $avis->getNote(),
            comment: (string) $avis->getDescription(),
            author: self::formatAuthorName($avis->getUtilisateur()),
        );
    }

    private static function formatAuthorName(?User $user): string
    {
        if ($user === null) {
            return 'Client anonyme';
        }

        $fullName = trim(sprintf('%s %s', (string) $user->getPrenom(), (string) $user->getNom()));

        return $fullName !== '' ? $fullName : 'Client anonyme';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'author' => $this->author,
        ];
    }
}
