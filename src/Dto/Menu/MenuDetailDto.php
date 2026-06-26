<?php

declare(strict_types=1);

namespace App\Dto\Menu;

use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Entity\Plat;

final readonly class MenuDetailDto
{
    /**
     * @param list<string> $images
     * @param list<MenuDishDto> $dishes
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $shortDescription,
        public string $fullDescription,
        public array $images,
        public string $theme,
        public string $diet,
        public int $minimumPeople,
        public string $price,
        public int $stock,
        public string $conditions,
        public array $dishes,
    ) {
    }

    public static function fromMenu(Menu $menu): self
    {
        $description = (string) $menu->getDescription();
        $shortDescription = mb_strlen($description) > 120
            ? mb_substr($description, 0, 117).'...'
            : $description;

        $dishTypes = [
            Plat::TYPE_ENTREE => 'Entrée',
            Plat::TYPE_PLAT => 'Plat',
            Plat::TYPE_DESSERT => 'Dessert',
        ];
        $dishes = [];
        foreach ($menu->getPlats() as $plat) {
            $typeKey = (string) ($plat->getTypePlat() ?? Plat::TYPE_PLAT);
            $type = $dishTypes[$typeKey] ?? 'Plat';
            $dishes[] = MenuDishDto::fromPlat($plat, $type);
        }

        $images = self::resolveImages($menu);

        return new self(
            id: (int) $menu->getId(),
            title: (string) $menu->getTitre(),
            shortDescription: $shortDescription,
            fullDescription: $description,
            images: array_values(array_unique($images)),
            theme: (string) $menu->getTheme()?->getLibelle(),
            diet: (string) $menu->getRegime()?->getLibelle(),
            minimumPeople: (int) $menu->getNombrePersonneMinimum(),
            price: number_format((float) $menu->getPrixParPersonne(), 2, '.', ''),
            stock: (int) $menu->getQuantiteRestante(),
            conditions: self::buildConditions($menu),
            dishes: $dishes,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'shortDescription' => $this->shortDescription,
            'fullDescription' => $this->fullDescription,
            'images' => $this->images,
            'theme' => $this->theme,
            'diet' => $this->diet,
            'minimumPeople' => $this->minimumPeople,
            'price' => $this->price,
            'stock' => $this->stock,
            'conditions' => $this->conditions,
            'dishes' => array_map(static fn (MenuDishDto $dish) => $dish->toArray(), $this->dishes),
        ];
    }

    public static function buildConditions(Menu $menu): string
    {
        return sprintf(
            'Commande minimum %d personnes. Délai de commande : 72 h avant la prestation. Stock restant : %d menu(s). Livraison gratuite à Bordeaux, frais de livraison hors Bordeaux. Réduction de 10 %% à partir de %d personnes.',
            $menu->getNombrePersonneMinimum(),
            $menu->getQuantiteRestante(),
            $menu->getNombrePersonneMinimum() + 5
        );
    }

    /**
     * @return list<string>
     */
    public static function resolveImages(Menu $menu): array
    {
        $images = [];
        foreach ($menu->getImages() as $menuImage) {
            $path = $menuImage->getPublicPath();
            if ($path !== null && $path !== '') {
                $images[] = $path;
            }
        }

        if ($images !== []) {
            return array_values(array_unique($images));
        }

        foreach ($menu->getPlats() as $plat) {
            $path = $plat->getPhotoPublicPath();
            if ($path !== null && $path !== '') {
                $images[] = $path;
            }
        }

        return array_values(array_unique($images));
    }
}
