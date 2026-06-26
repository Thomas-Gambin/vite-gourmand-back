<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Entity\Plat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class PlatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Plat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('plat')
            ->setEntityLabelInPlural('plats')
            ->setPageTitle(Crud::PAGE_INDEX, 'Plats')
            ->setDefaultSort(['titrePlat' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titrePlat', 'Nom du plat');
        yield ChoiceField::new('typePlat', 'Type')
            ->setChoices([
                'Entrée' => Plat::TYPE_ENTREE,
                'Plat' => Plat::TYPE_PLAT,
                'Dessert' => Plat::TYPE_DESSERT,
            ]);
        yield ImageField::new('photo', 'Photo')
            ->setBasePath('/uploads/plats/')
            ->setUploadDir('public/uploads/plats/')
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->maxSize('5M')
            ->setHelp('Cliquez sur « Choisir un fichier » pour ajouter une photo. Formats acceptés : JPG, PNG, WebP (max. 5 Mo).');
        yield AssociationField::new('allergenes', 'Allergènes');
        yield AssociationField::new('menus', 'Menus associés')->onlyOnDetail();
    }
}
