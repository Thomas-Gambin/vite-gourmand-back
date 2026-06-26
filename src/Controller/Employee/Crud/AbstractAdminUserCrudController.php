<?php

declare(strict_types=1);

namespace App\Controller\Employee\Crud;

use App\Entity\User;
use App\Service\Admin\AdminUserPersistenceService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

#[IsGranted('ROLE_ADMIN')]
abstract class AbstractAdminUserCrudController extends AbstractCrudController
{
    protected ?string $submittedPlainPassword = null;

    public function __construct(
        protected readonly AdminUserPersistenceService $adminUserPersistenceService,
    ) {
    }

    abstract protected function getManagedRoleLibelle(): string;

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setSearchFields(['email', 'nom', 'prenom', 'telephone', 'ville'])
            ->setDefaultSort(['nom' => 'ASC', 'prenom' => 'ASC'])
            ->showEntityActionsInlined(false)
            ->setPaginatorPageSize(15);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function createEntity(string $entityFqcn): object
    {
        $user = new User();
        $user->setRole($this->adminUserPersistenceService->getRoleByLibelle($this->getManagedRoleLibelle()));

        return $user;
    }

    public function configureFields(string $pageName): iterable
    {
        $isNew = $pageName === Crud::PAGE_NEW;
        $isIndex = $pageName === Crud::PAGE_INDEX;

        yield TextField::new('email', 'Email')
            ->setCssClass('vg-user-email');

        yield TextField::new('prenom', $isIndex ? 'Nom' : 'Prénom')
            ->formatValue(static function (?string $value, User $user) use ($isIndex): string {
                if (!$isIndex) {
                    return (string) $user->getPrenom();
                }

                return sprintf('%s %s', $user->getPrenom(), $user->getNom());
            });

        yield TextField::new('nom', 'Nom')
            ->hideOnIndex();

        yield TextField::new('telephone', 'Téléphone');

        yield TextField::new('ville', 'Ville')
            ->hideOnIndex();

        yield TextField::new('pays', 'Pays')
            ->hideOnIndex();

        yield TextField::new('adressePostale', 'Adresse postale')
            ->hideOnIndex();

        yield TextField::new('plainPassword', 'Mot de passe')
            ->onlyOnForms()
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => $isNew,
                'constraints' => $this->getPasswordConstraints($isNew),
                'help' => $isNew
                    ? 'Minimum 8 caractères, avec majuscule, minuscule, chiffre et caractère spécial.'
                    : 'Laissez vide pour conserver le mot de passe actuel.',
            ]);

        yield BooleanField::new('isVerified', 'Compte confirmé')
            ->hideOnForm()
            ->onlyOnDetail();

        yield TextField::new('accountStatus', 'Statut')
            ->onlyOnIndex()
            ->setValue('')
            ->formatValue(static fn ($value, User $user): string => $user->isVerified()
                ? '<span class="badge badge-success">Confirmé</span>'
                : '<span class="badge badge-warning">En attente</span>')
            ->renderAsHtml();

        yield DateTimeField::new('verifiedAt', 'Confirmé le')
            ->hideOnForm()
            ->hideOnIndex();
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->join('entity.role', 'managedRole')
            ->andWhere('managedRole.libelle = :managedRoleLibelle')
            ->setParameter('managedRoleLibelle', $this->getManagedRoleLibelle());
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        return $this->addPlainPasswordListener(parent::createNewFormBuilder($entityDto, $formOptions, $context));
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        return $this->addPlainPasswordListener(parent::createEditFormBuilder($entityDto, $formOptions, $context));
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $plainPassword = $this->submittedPlainPassword;
        if ($plainPassword === null || $plainPassword === '') {
            throw new \RuntimeException('Le mot de passe est obligatoire.');
        }

        $this->adminUserPersistenceService->prepareNewUser(
            $entityInstance,
            $plainPassword,
            $this->getManagedRoleLibelle(),
        );

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $entityInstance->setRole($this->adminUserPersistenceService->getRoleByLibelle($this->getManagedRoleLibelle()));
        $this->adminUserPersistenceService->updateUserPassword($entityInstance, $this->submittedPlainPassword);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::deleteEntity($entityManager, $entityInstance);

            return;
        }

        $currentAdmin = $this->getUser();
        if (!$currentAdmin instanceof User) {
            throw new \RuntimeException('Utilisateur admin introuvable.');
        }

        try {
            $this->adminUserPersistenceService->assertDeletable($entityInstance, $currentAdmin);
        } catch (\InvalidArgumentException $exception) {
            throw new EntityRemoveException([
                'entity_name' => 'utilisateur',
                'message' => $exception->getMessage(),
            ]);
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    /**
     * @return list<Assert\NotBlank|Assert\Length|Assert\Regex>
     */
    private function getPasswordConstraints(bool $required): array
    {
        $constraints = [
            new Assert\Length(
                min: 8,
                minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
            ),
            new Assert\Regex(
                pattern: '/[A-Z]/',
                message: 'Le mot de passe doit contenir au moins une majuscule.',
            ),
            new Assert\Regex(
                pattern: '/[a-z]/',
                message: 'Le mot de passe doit contenir au moins une minuscule.',
            ),
            new Assert\Regex(
                pattern: '/\d/',
                message: 'Le mot de passe doit contenir au moins un chiffre.',
            ),
            new Assert\Regex(
                pattern: '/[^A-Za-z0-9]/',
                message: 'Le mot de passe doit contenir au moins un caractère spécial.',
            ),
        ];

        if ($required) {
            array_unshift($constraints, new Assert\NotBlank(message: 'Le mot de passe est obligatoire.'));
        }

        return $constraints;
    }

    private function addPlainPasswordListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            if (!$event->getForm()->has('plainPassword')) {
                return;
            }

            $plainPassword = $event->getForm()->get('plainPassword')->getData();
            if (!\is_string($plainPassword) || $plainPassword === '') {
                return;
            }

            $user = $event->getData();
            if ($user instanceof User) {
                $user->setPassword($plainPassword);
                $this->submittedPlainPassword = $plainPassword;
            }
        });

        return $formBuilder;
    }
}
