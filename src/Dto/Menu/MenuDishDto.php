<?php

declare(strict_types=1);

namespace App\Dto\Menu;

use App\Entity\Menu;
use App\Entity\Plat;

final readonly class MenuDishDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public ?string $description,
        /** @var list<array{id: int, name: string}> */
        public array $allergens,
    ) {
    }

    public static function fromPlat(Plat $plat, string $type): self
    {
        $allergens = [];
        foreach ($plat->getAllergenes() as $allergene) {
            $allergens[] = [
                'id' => (int) $allergene->getId(),
                'name' => (string) $allergene->getLibelle(),
            ];
        }

        return new self(
            id: (int) $plat->getId(),
            name: (string) $plat->getTitrePlat(),
            type: $type,
            description: null,
            allergens: $allergens,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'allergens' => $this->allergens,
        ];
    }
}
