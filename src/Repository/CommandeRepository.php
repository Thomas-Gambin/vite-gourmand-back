<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findLastSequenceForYear(string $year): int
    {
        $prefix = sprintf('CMD-%s-', $year);

        $result = $this->createQueryBuilder('c')
            ->select('c.numeroCommande')
            ->where('c.numeroCommande LIKE :prefix')
            ->setParameter('prefix', $prefix.'%')
            ->orderBy('c.numeroCommande', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || !isset($result['numeroCommande'])) {
            return 0;
        }

        $suffix = substr((string) $result['numeroCommande'], strlen($prefix));

        return is_numeric($suffix) ? (int) $suffix : 0;
    }
}
