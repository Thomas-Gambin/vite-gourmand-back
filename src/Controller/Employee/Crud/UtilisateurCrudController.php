<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Entity\User;
use App\Service\Admin\AdminUserPersistenceService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class UtilisateurCrudController extends AbstractAdminUserCrudController
{
    public function __construct(
        AdminUserPersistenceService $adminUserPersistenceService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
        parent::__construct($adminUserPersistenceService);
    }

    protected function getManagedRoleLibelle(): string
    {
        return 'ROLE_USER';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('utilisateur')
            ->setEntityLabelInPlural('utilisateurs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs');
    }

    public function configureActions(Actions $actions): Actions
    {
        $promoteToEmployee = Action::new('promoteToEmployee', 'Passer en employé', 'fa fa-user-tie')
            ->linkToCrudAction('promoteToEmployee')
            ->displayIf(static fn (User $user) => $user->getRole()?->getLibelle() === 'ROLE_USER');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $promoteToEmployee)
            ->add(Crud::PAGE_DETAIL, $promoteToEmployee);
    }

    #[AdminRoute(path: '/{entityId}/promote-to-employee', name: 'promote_to_employee')]
    public function promoteToEmployee(#[MapEntity(id: 'entityId')] User $user): Response
    {
        try {
            $this->adminUserPersistenceService->promoteToEmployee($user);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                sprintf('%s %s est désormais un employé.', $user->getPrenom(), $user->getNom()),
            );
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(EmployeCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
