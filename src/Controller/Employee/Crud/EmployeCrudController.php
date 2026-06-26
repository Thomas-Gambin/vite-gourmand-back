<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class EmployeCrudController extends AbstractAdminUserCrudController
{
    protected function getManagedRoleLibelle(): string
    {
        return 'ROLE_EMPLOYEE';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('employé')
            ->setEntityLabelInPlural('employés')
            ->setPageTitle(Crud::PAGE_INDEX, 'Employés');
    }
}
