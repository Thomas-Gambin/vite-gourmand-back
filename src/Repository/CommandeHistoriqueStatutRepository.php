<?php

namespace App\Repository;

use App\Entity\CommandeHistoriqueStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeHistoriqueStatut>
 */
class CommandeHistoriqueStatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeHistoriqueStatut::class);
    }
}
