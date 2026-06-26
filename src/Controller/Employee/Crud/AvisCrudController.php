<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\User;
use App\Service\AvisModerationService;
use App\Service\AvisStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

final class AvisCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AvisModerationService $avisModerationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Avis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('avis')
            ->setEntityLabelInPlural('avis')
            ->setPageTitle(Crud::PAGE_INDEX, 'Avis clients')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $validate = Action::new('validateAvis', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validateAvis')
            ->setCssClass('btn btn-success')
            ->displayIf(static fn (Avis $avis) => AvisStatus::isModeratable((string) $avis->getStatut()));

        $reject = Action::new('rejectAvis', 'Refuser', 'fa fa-times')
            ->linkToCrudAction('rejectAvis')
            ->setCssClass('btn btn-danger')
            ->displayIf(static fn (Avis $avis) => AvisStatus::isModeratable((string) $avis->getStatut()));

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $validate)
            ->add(Crud::PAGE_INDEX, $reject)
            ->add(Crud::PAGE_DETAIL, $validate)
            ->add(Crud::PAGE_DETAIL, $reject);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(ChoiceFilter::new('statut')->setChoices(AvisStatus::labels()));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('note', 'Note');
        yield TextareaField::new('description', 'Commentaire');
        yield TextField::new('menuTitre', 'Menu')
            ->formatValue(static fn (?string $titre) => $titre ?? 'N/A');
        yield AssociationField::new('utilisateur', 'Client')
            ->formatValue(static fn (?User $user) => $user ? sprintf('%s %s', $user->getPrenom(), $user->getNom()) : '');
        yield AssociationField::new('commande', 'Commande')
            ->formatValue(static fn (?Commande $commande) => $commande?->getNumeroCommande());
        yield TextField::new('statut', 'Statut')
            ->formatValue(static fn (?string $value) => sprintf(
                '<span class="badge badge-%s">%s</span>',
                AvisStatus::badgeClass((string) $value),
                AvisStatus::label((string) $value),
            ))
            ->renderAsHtml();
    }

    #[AdminRoute(path: '/{entityId}/validate', name: 'validate_avis')]
    public function validateAvis(#[MapEntity(id: 'entityId')] Avis $avis): Response
    {
        return $this->moderateAvis($avis, true);
    }

    #[AdminRoute(path: '/{entityId}/reject', name: 'reject_avis')]
    public function rejectAvis(#[MapEntity(id: 'entityId')] Avis $avis): Response
    {
        return $this->moderateAvis($avis, false);
    }

    private function moderateAvis(Avis $avis, bool $validate): Response
    {
        try {
            if ($validate) {
                $this->avisModerationService->valider($avis);
                $this->addFlash('success', 'Avis validé avec succès.');
            } else {
                $this->avisModerationService->refuser($avis);
                $this->addFlash('success', 'Avis refusé.');
            }
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
