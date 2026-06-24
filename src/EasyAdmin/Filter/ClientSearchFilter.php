<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ClientSearchFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName = 'clientSearch', ?string $label = 'Client'): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $value = trim((string) $filterDataDto->getValue());
        if ($value === '') {
            return;
        }

        $alias = $filterDataDto->getEntityAlias();
        $clientAlias = $filterDataDto->getFormName().'_client';

        $queryBuilder
            ->leftJoin(sprintf('%s.utilisateur', $alias), $clientAlias)
            ->andWhere(sprintf(
                '%s.nom LIKE :clientSearch OR %s.prenom LIKE :clientSearch OR %s.email LIKE :clientSearch OR %s.telephone LIKE :clientSearch',
                $clientAlias,
                $clientAlias,
                $clientAlias,
                $clientAlias,
            ))
            ->setParameter('clientSearch', '%'.$value.'%');
    }
}
