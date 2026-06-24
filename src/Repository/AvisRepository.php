<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Service\AvisStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function findOneByCommande(Commande $commande): ?Avis
    {
        return $this->findOneBy(['commande' => $commande]);
    }

    /**
     * @return list<Avis>
     */
    public function findValidatedByMenuId(int $menuId): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.commande', 'c')
            ->innerJoin('c.menu', 'm')
            ->innerJoin('a.utilisateur', 'u')
            ->addSelect('u')
            ->where('m.id = :menuId')
            ->andWhere('a.statut = :statut')
            ->setParameter('menuId', $menuId)
            ->setParameter('statut', AvisStatus::VALIDE)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
