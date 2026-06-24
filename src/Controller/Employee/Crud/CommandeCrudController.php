<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Dto\Commande\EmployeeContactPayload;
use App\EasyAdmin\Filter\ClientSearchFilter;
use App\Entity\Commande;
use App\Entity\User;
use App\Form\Employee\CancelCommandeType;
use App\Form\Employee\ChangeCommandeStatusType;
use App\Service\CommandeStatus;
use App\Service\EmployeeCommandeService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class CommandeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EmployeeCommandeService $employeeCommandeService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('commande')
            ->setEntityLabelInPlural('commandes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Commandes')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Commande $commande) => sprintf('Commande %s', $commande->getNumeroCommande()))
            ->setDefaultSort(['dateCommande' => 'DESC', 'id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $changeStatus = Action::new('changeStatus', 'Changer le statut', 'fa fa-sync')
            ->linkToCrudAction('changeStatus')
            ->displayIf(static fn (Commande $commande) => $commande->getStatut() !== CommandeStatus::ANNULEE);

        $cancelOrder = Action::new('cancelOrder', 'Annuler', 'fa fa-ban')
            ->linkToCrudAction('cancelOrder')
            ->setCssClass('btn btn-danger')
            ->displayIf(static fn (Commande $commande) => $commande->getStatut() !== CommandeStatus::ANNULEE);

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $changeStatus)
            ->add(Crud::PAGE_DETAIL, $cancelOrder)
            ->add(Crud::PAGE_INDEX, $changeStatus)
            ->add(Crud::PAGE_INDEX, $cancelOrder);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut')->setChoices(CommandeStatus::labels()))
            ->add(ClientSearchFilter::new());
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('numeroCommande', 'N° commande');
        yield AssociationField::new('utilisateur', 'Client')
            ->formatValue(static fn (?User $user) => $user ? sprintf('%s %s', $user->getPrenom(), $user->getNom()) : '');
        yield AssociationField::new('menu', 'Menu')
            ->formatValue(static fn ($menu) => $menu?->getTitre());
        yield DateTimeField::new('dateCommande', 'Date de commande')
            ->setFormat('dd/MM/yyyy HH:mm');
        yield DateField::new('datePrestation', 'Date de prestation');
        yield TextField::new('statut', 'Statut')
            ->formatValue(static fn (?string $value) => sprintf(
                '<span class="badge badge-%s">%s</span>',
                CommandeStatus::badgeClass((string) $value),
                CommandeStatus::label((string) $value),
            ))
            ->renderAsHtml();
        yield MoneyField::new('prixMenu', 'Prix menu')->setCurrency('EUR')->onlyOnIndex();

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('heureLivraison', 'Heure de livraison');
            yield MoneyField::new('prixLivraison', 'Prix livraison')->setCurrency('EUR');
            yield IntegerField::new('nombrePersonne', 'Nombre de personnes');
            yield BooleanField::new('pretMateriel', 'Prêt de matériel');
            yield BooleanField::new('restitutionMateriel', 'Restitution matériel');
            yield TextField::new('adressePrestation', 'Adresse de prestation');
            yield TextField::new('villePrestation', 'Ville');
            yield TextField::new('codePostalPrestation', 'Code postal');
            yield TextField::new('utilisateur.email', 'Email client')->onlyOnDetail();
            yield TextField::new('utilisateur.telephone', 'Téléphone client')->onlyOnDetail();
        }

        // Réservés au formulaire d'annulation — ne pas afficher dans le CRUD
        yield TextField::new('contactMode', 'Mode de contact')->hideOnIndex()->hideOnDetail();
        yield TextField::new('employeeActionReason', 'Motif employé')->hideOnIndex()->hideOnDetail();
        yield DateTimeField::new('contactedAt', 'Date de contact')->hideOnIndex()->hideOnDetail();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.utilisateur', 'client')->addSelect('client')
            ->leftJoin('entity.menu', 'menu')->addSelect('menu');

        if ([] === $searchDto->getCustomSort()) {
            $queryBuilder
                ->orderBy('entity.dateCommande', 'DESC')
                ->addOrderBy('entity.id', 'DESC');
        }

        return $queryBuilder;
    }

    #[AdminRoute(path: '/{entityId}/change-status', name: 'change_status')]
    public function changeStatus(#[MapEntity(id: 'entityId')] Commande $commande, Request $request): Response
    {
        $currentStatut = (string) $commande->getStatut();
        $form = $this->createForm(ChangeCommandeStatusType::class, ['statut' => $currentStatut], [
            'current_statut' => $currentStatut,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $statut = (string) $form->get('statut')->getData();
            if ($statut === CommandeStatus::ANNULEE) {
                $this->addFlash('danger', 'Utilisez l\'action « Annuler » pour annuler une commande.');

                return $this->redirect($this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId((string) $commande->getId())
                    ->generateUrl());
            }

            try {
                $this->employeeCommandeService->changerStatut($commande, $statut, $user);
                $this->entityManager->flush();
                $this->addFlash('success', 'Statut mis à jour avec succès.');
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId((string) $commande->getId())
                ->generateUrl());
        }

        return $this->renderCommandeAction('employee/commande/change_status.html.twig', $commande, $form);
    }

    #[AdminRoute(path: '/{entityId}/cancel', name: 'cancel_order')]
    public function cancelOrder(#[MapEntity(id: 'entityId')] Commande $commande, Request $request): Response
    {
        $form = $this->createForm(CancelCommandeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $data = $form->getData();
            try {
                $this->employeeCommandeService->changerStatut(
                    $commande,
                    CommandeStatus::ANNULEE,
                    $user,
                    new EmployeeContactPayload(
                        contactMode: $data['contactMode'] ?? null,
                        employeeActionReason: $data['employeeActionReason'] ?? null,
                        contactedAt: $data['contactedAt'] ?? null,
                    ),
                );
                $this->entityManager->flush();
                $this->addFlash('success', 'Commande annulée avec succès.');
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('danger', $exception->getMessage());

                return $this->renderCommandeAction('employee/commande/cancel.html.twig', $commande, $form);
            }

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId((string) $commande->getId())
                ->generateUrl());
        }

        return $this->renderCommandeAction('employee/commande/cancel.html.twig', $commande, $form);
    }

    private function renderCommandeAction(string $view, Commande $commande, \Symfony\Component\Form\FormInterface $form): Response
    {
        return $this->render($view, [
            'commande' => $commande,
            'form' => $form,
            'statusLabel' => CommandeStatus::label((string) $commande->getStatut()),
            'statusBadge' => CommandeStatus::badgeClass((string) $commande->getStatut()),
        ]);
    }
}
