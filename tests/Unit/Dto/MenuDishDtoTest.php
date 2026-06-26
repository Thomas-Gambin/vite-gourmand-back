<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Menu;

use App\Dto\Menu\MenuDishDto;
use App\Entity\Allergene;
use App\Entity\Plat;
use PHPUnit\Framework\TestCase;

final class MenuDishDtoTest extends TestCase
{
    public function testFromPlatIncludesPhotoPublicPath(): void
    {
        $allergene = new Allergene();
        $allergene->setLibelle('Lait');

        $plat = new Plat();
        $plat->setTitrePlat('Tarte citron');
        $plat->setTypePlat(Plat::TYPE_DESSERT);
        $plat->setPhoto('tarte-citron.jpg');
        $plat->addAllergene($allergene);

        $dto = MenuDishDto::fromPlat($plat, 'Dessert');

        self::assertSame('/uploads/plats/tarte-citron.jpg', $dto->photo);
        self::assertSame('/uploads/plats/tarte-citron.jpg', $dto->toArray()['photo']);
    }
}
