<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\Employee\Crud\AvisCrudController;
use App\Controller\Employee\Crud\CommandeCrudController;
use App\Controller\Employee\Crud\EmployeCrudController;
use App\Controller\Employee\Crud\HoraireCrudController;
use App\Controller\Employee\Crud\MenuCrudController;
use App\Controller\Employee\Crud\PlatCrudController;
use App\Controller\Employee\Crud\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin_dashboard')]
final class EmployeeDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('employee/dashboard/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Vite & Gourmand')
            ->setTranslationDomain('EasyAdminBundle')
            ->setDefaultColorScheme(ColorScheme::LIGHT);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('css/admin-vite-gourmand.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkTo(CommandeCrudController::class, 'Commandes', 'fa fa-shopping-cart');
        yield MenuItem::linkTo(MenuCrudController::class, 'Menus', 'fa fa-utensils');
        yield MenuItem::linkTo(PlatCrudController::class, 'Plats', 'fa fa-drumstick-bite');
        yield MenuItem::linkTo(HoraireCrudController::class, 'Horaires', 'fa fa-clock');
        yield MenuItem::linkTo(AvisCrudController::class, 'Avis', 'fa fa-star');
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fa fa-users')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkTo(EmployeCrudController::class, 'Employés', 'fa fa-user-tie')
            ->setPermission('ROLE_ADMIN');
    }
}
