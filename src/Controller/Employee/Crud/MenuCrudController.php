<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Dto\Menu\MenuDetailDto;
use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Form\Employee\MenuImageType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\TextAlign;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/admin-menu-plats.js')
            ->addJsFile('js/admin-menu-images.js')
            ->addJsFile(Asset::fromEasyAdminAssetPackage('field-file-upload.js')->onlyOnForms());
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

        $imagesField = CollectionField::new('images', 'Images du menu')
            ->setCssClass('vg-menu-images-field')
            ->setHelp('Cliquez sur « Choisir une photo » pour sélectionner un fichier. Vous pouvez en ajouter plusieurs. Formats acceptés : JPG, PNG, WebP (max. 5 Mo).')
            ->hideOnIndex();

        if ($pageName === Crud::PAGE_DETAIL) {
            $imagesField
                ->formatValue(static function ($value, Menu $menu): string {
                    $thumbnails = [];
                    foreach ($menu->getImages() as $image) {
                        $path = $image->getPublicPath();
                        if ($path === null || $path === '') {
                            continue;
                        }

                        $thumbnails[] = sprintf(
                            '<img src="%s" alt="" class="vg-menu-admin-thumb">',
                            htmlspecialchars($path, \ENT_QUOTES),
                        );
                    }

                    return $thumbnails !== []
                        ? sprintf('<div class="vg-menu-images-gallery">%s</div>', implode('', $thumbnails))
                        : '<span class="text-muted">Aucune image</span>';
                })
                ->renderAsHtml();
        } else {
            $imagesField
                ->setEntryType(MenuImageType::class)
                ->setFormTypeOption('by_reference', false)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->setEntryIsComplex()
                ->showEntryLabel(false)
                ->setEntryToStringMethod(static fn (): string => 'Photo');
        }

        yield $imagesField;

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

        yield AssociationField::new('plats', $isIndex ? 'Plats' : 'Plats associés')
            ->formatValue(static function ($value, Menu $menu) use ($isIndex): string {
                if ($isIndex) {
                    return self::renderPlatsIndexButton($menu);
                }

                $names = array_map(
                    static fn (Plat $plat): string => htmlspecialchars((string) $plat->getTitrePlat(), \ENT_QUOTES),
                    $menu->getPlats()->toArray(),
                );

                return $names !== [] ? implode(', ', $names) : '—';
            })
            ->renderAsHtml()
            ->setTextAlign($isIndex ? TextAlign::CENTER : TextAlign::LEFT);

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

    private static function renderPlatsIndexButton(Menu $menu): string
    {
        $plats = $menu->getPlats()->toArray();
        $count = \count($plats);
        $titles = array_map(
            static fn (Plat $plat): string => (string) $plat->getTitrePlat(),
            $plats,
        );

        return sprintf(
            '<button type="button" class="btn btn-sm btn-outline-primary vg-menu-plats-btn" data-menu-title="%s" data-plats="%s" aria-label="Voir les %d plats du menu %s"><i class="fa fa-utensils me-1" aria-hidden="true"></i>%d</button>',
            htmlspecialchars((string) $menu->getTitre(), \ENT_QUOTES),
            htmlspecialchars(json_encode($titles, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE), \ENT_QUOTES),
            $count,
            htmlspecialchars((string) $menu->getTitre(), \ENT_QUOTES),
            $count,
        );
    }
}
