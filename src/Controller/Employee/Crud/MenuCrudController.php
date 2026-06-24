<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Dto\Menu\MenuDetailDto;
use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\TextAlign;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class MenuCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Menu::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('menu')
            ->setEntityLabelInPlural('menus')
            ->setPageTitle(Crud::PAGE_INDEX, 'Menus')
            ->setDefaultSort(['titre' => 'ASC'])
            ->setSearchFields(['titre', 'description'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        $isIndex = $pageName === Crud::PAGE_INDEX;

        yield TextField::new('titre', 'Titre')
            ->setCssClass('vg-menu-title');

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex();

        yield IntegerField::new('nombrePersonneMinimum', $isIndex ? 'Min.' : 'Nombre minimum de personnes')
            ->setTextAlign(TextAlign::CENTER);

        yield MoneyField::new('prixParPersonne', $isIndex ? 'Prix / pers.' : 'Prix par personne')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setTextAlign(TextAlign::CENTER);

        yield IntegerField::new('quantiteRestante', $isIndex ? 'Stock' : 'Stock disponible')
            ->formatValue(static function (?int $value): string {
                $stock = $value ?? 0;
                $badge = $stock <= 3 ? 'danger' : ($stock <= 8 ? 'warning' : 'success');

                return sprintf('<span class="badge badge-%s">%d</span>', $badge, $stock);
            })
            ->setTextAlign(TextAlign::CENTER);

        yield AssociationField::new('theme', 'Thème')
            ->formatValue(static function (?Theme $theme): string {
                if (!$theme instanceof Theme || $theme->getLibelle() === null) {
                    return '—';
                }

                return sprintf(
                    '<span class="badge badge-primary">%s</span>',
                    htmlspecialchars($theme->getLibelle(), \ENT_QUOTES),
                );
            })
            ->renderAsHtml();

        yield AssociationField::new('regime', 'Régime')
            ->formatValue(static function (?Regime $regime): string {
                if (!$regime instanceof Regime || $regime->getLibelle() === null) {
                    return '—';
                }

                return sprintf(
                    '<span class="badge badge-secondary">%s</span>',
                    htmlspecialchars($regime->getLibelle(), \ENT_QUOTES),
                );
            })
            ->renderAsHtml();

        yield AssociationField::new('plats', 'Plats associés')
            ->hideOnIndex()
            ->formatValue(static function ($value, Menu $menu): string {
                $names = array_map(
                    static fn (Plat $plat): string => htmlspecialchars((string) $plat->getTitrePlat(), \ENT_QUOTES),
                    $menu->getPlats()->toArray(),
                );

                return $names !== [] ? implode(', ', $names) : '—';
            })
            ->renderAsHtml();

        yield TextField::new('nombrePlats', 'Plats')
            ->onlyOnIndex()
            ->formatValue(static function ($value, Menu $menu): string {
                $titles = array_map(
                    static fn (Plat $plat): string => htmlspecialchars((string) $plat->getTitrePlat(), \ENT_QUOTES),
                    $menu->getPlats()->toArray(),
                );

                return sprintf(
                    '<span class="badge badge-info" title="%s">%d</span>',
                    implode(', ', $titles),
                    $menu->getPlats()->count(),
                );
            })
            ->renderAsHtml()
            ->setTextAlign(TextAlign::CENTER);

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextareaField::new('id', 'Conditions de commande')
                ->onlyOnDetail()
                ->formatValue(static fn ($value, Menu $menu) => MenuDetailDto::buildConditions($menu));
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.theme', 'menuTheme')->addSelect('menuTheme')
            ->leftJoin('entity.regime', 'menuRegime')->addSelect('menuRegime')
            ->leftJoin('entity.plats', 'menuPlats')->addSelect('menuPlats');
    }
}
