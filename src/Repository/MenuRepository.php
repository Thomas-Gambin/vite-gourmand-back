<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * @return list<Menu>
     */
    public function findAllForApi(): array
    {
        return $this->createQueryBuilder('menu')
            ->leftJoin('menu.images', 'menuImages')->addSelect('menuImages')
            ->leftJoin('menu.plats', 'menuPlats')->addSelect('menuPlats')
            ->leftJoin('menu.theme', 'menuTheme')->addSelect('menuTheme')
            ->leftJoin('menu.regime', 'menuRegime')->addSelect('menuRegime')
            ->orderBy('menu.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneForApi(int $id): ?Menu
    {
        return $this->createQueryBuilder('menu')
            ->leftJoin('menu.images', 'menuImages')->addSelect('menuImages')
            ->leftJoin('menu.plats', 'menuPlats')->addSelect('menuPlats')
            ->leftJoin('menu.theme', 'menuTheme')->addSelect('menuTheme')
            ->leftJoin('menu.regime', 'menuRegime')->addSelect('menuRegime')
            ->andWhere('menu.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
