<?php

namespace App\Repository;

use App\Entity\Horaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Horaire>
 */
class HoraireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Horaire::class);
    }

    /**
     * @return list<Horaire>
     */
    public function findAllOrderedByWeekDay(): array
    {
        $order = array_flip([
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche',
        ]);

        $horaires = $this->findAll();

        usort(
            $horaires,
            static fn (Horaire $left, Horaire $right): int => ($order[$left->getJour() ?? ''] ?? 99) <=> ($order[$right->getJour() ?? ''] ?? 99),
        );

        return $horaires;
    }
}
