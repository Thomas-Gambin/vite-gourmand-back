<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class HoraireCrudController extends AbstractCrudController
{
    private const JOURS = [
        'Lundi' => 'Lundi',
        'Mardi' => 'Mardi',
        'Mercredi' => 'Mercredi',
        'Jeudi' => 'Jeudi',
        'Vendredi' => 'Vendredi',
        'Samedi' => 'Samedi',
        'Dimanche' => 'Dimanche',
    ];

    public function __construct(
        private readonly HoraireRepository $horaireRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Horaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('horaire')
            ->setEntityLabelInPlural('horaires')
            ->setPageTitle(Crud::PAGE_INDEX, 'Horaires')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        if ($this->horaireRepository->count([]) >= 7) {
            $actions = $actions->disable(Action::NEW);
        }

        return $actions->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('jour', 'Jour')
            ->setChoices(self::JOURS)
            ->setFormTypeOption('disabled', $pageName === Crud::PAGE_EDIT);
        yield TextField::new('heureOuverture', 'Heure d\'ouverture');
        yield TextField::new('heureFermeture', 'Heure de fermeture');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->addOrderBy('entity.id', 'ASC');
    }
}
