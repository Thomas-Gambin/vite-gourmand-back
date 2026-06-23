<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\User;
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

    /**
     * @return list<Commande>
     */
    public function findByUtilisateur(User $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->where('c.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdAndUtilisateur(int $id, User $utilisateur): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.utilisateur', 'u')
            ->addSelect('u')
            ->leftJoin('c.historiqueStatuts', 'h')
            ->addSelect('h')
            ->leftJoin('c.avis', 'a')
            ->addSelect('a')
            ->where('c.id = :id')
            ->andWhere('c.utilisateur = :utilisateur')
            ->setParameter('id', $id)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<Commande>
     */
    public function findReviewableByUtilisateur(User $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.menu', 'm')
            ->addSelect('m')
            ->leftJoin('c.avis', 'a')
            ->where('c.utilisateur = :utilisateur')
            ->andWhere('c.statut = :statut')
            ->andWhere('a.id IS NULL')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('statut', 'terminee')
            ->orderBy('c.datePrestation', 'DESC')
            ->getQuery()
            ->getResult();
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
