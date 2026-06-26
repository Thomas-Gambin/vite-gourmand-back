<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Menu;

use App\Dto\Menu\MenuDetailDto;
use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use PHPUnit\Framework\TestCase;

final class MenuDetailDtoTest extends TestCase
{
    public function testResolveImagesPrefersMenuImagesOverPlatPhotos(): void
    {
        $menu = $this->createMenu();

        $menuImage = new MenuImage();
        $menuImage->setFilename('menu-cover.jpg');
        $menu->addImage($menuImage);

        $plat = new Plat();
        $plat->setTitrePlat('Entrée test');
        $plat->setTypePlat(Plat::TYPE_ENTREE);
        $plat->setPhoto('entree.jpg');
        $menu->addPlat($plat);

        self::assertSame(['/uploads/menus/menu-cover.jpg'], MenuDetailDto::resolveImages($menu));
    }

    public function testResolveImagesFallsBackToPlatPhotosWhenMenuHasNoImages(): void
    {
        $menu = $this->createMenu();

        $plat = new Plat();
        $plat->setTitrePlat('Dessert test');
        $plat->setTypePlat(Plat::TYPE_DESSERT);
        $plat->setPhoto('dessert.jpg');
        $menu->addPlat($plat);

        self::assertSame(['/uploads/plats/dessert.jpg'], MenuDetailDto::resolveImages($menu));
    }

    private function createMenu(): Menu
    {
        $theme = new Theme();
        $theme->setLibelle('Classique');

        $regime = new Regime();
        $regime->setLibelle('Classique');

        $menu = new Menu();
        $menu->setTitre('Menu test');
        $menu->setDescription('Description test');
        $menu->setNombrePersonneMinimum(2);
        $menu->setPrixParPersonne('25.00');
        $menu->setQuantiteRestante(5);
        $menu->setTheme($theme);
        $menu->setRegime($regime);

        return $menu;
    }
}
