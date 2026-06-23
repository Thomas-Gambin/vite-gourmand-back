<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CommandeRepository;

final class CommandeNumberGenerator
{
    public function __construct(
        private readonly CommandeRepository $commandeRepository,
    ) {
    }

    public function generate(): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        $lastSequence = $this->commandeRepository->findLastSequenceForYear($year);

        return sprintf('CMD-%s-%04d', $year, $lastSequence + 1);
    }
}
