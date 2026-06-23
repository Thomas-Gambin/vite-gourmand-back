<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Commande;
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
}
