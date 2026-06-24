<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\Commande\EmployeeContactPayload;
use App\Entity\Commande;
use App\Entity\User;
use App\Service\CommandeAnnulationService;
use App\Service\CommandeHistoriqueService;
use App\Service\CommandeStatutService;
use App\Service\CommandeStatus;
use App\Service\EmployeeCommandeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class EmployeeCommandeServiceTest extends TestCase
{
    public function testChangerStatutAutoriseTransitionSimple(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $commande = $this->createCommande(CommandeStatus::ACCEPTE);
        $user = new User();

        $service = new EmployeeCommandeService(
            new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
            new CommandeAnnulationService(
                new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
                $entityManager,
            ),
        );

        $service->changerStatut($commande, CommandeStatus::EN_PREPARATION, $user);

        self::assertSame(CommandeStatus::EN_PREPARATION, $commande->getStatut());
    }

    public function testChangerStatutRefuseStatutEmployeInvalide(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new EmployeeCommandeService(
            new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
            new CommandeAnnulationService(
                new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
                $entityManager,
            ),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->changerStatut($this->createCommande(CommandeStatus::EN_ATTENTE), CommandeStatus::EN_ATTENTE, new User());
    }

    public function testChangerStatutAnnulationExigeContact(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new EmployeeCommandeService(
            new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
            new CommandeAnnulationService(
                new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
                $entityManager,
            ),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->changerStatut($this->createCommande(CommandeStatus::ACCEPTE), CommandeStatus::ANNULEE, new User());
    }

    public function testChangerStatutAnnulationAvecContact(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $commande = $this->createCommande(CommandeStatus::ACCEPTE);
        $contact = new EmployeeContactPayload('gsm', 'Client absent', new \DateTimeImmutable());

        $service = new EmployeeCommandeService(
            new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
            new CommandeAnnulationService(
                new CommandeStatutService(new CommandeHistoriqueService($entityManager)),
                $entityManager,
            ),
        );

        $service->changerStatut($commande, CommandeStatus::ANNULEE, new User(), $contact);

        self::assertSame(CommandeStatus::ANNULEE, $commande->getStatut());
        self::assertSame('gsm', $commande->getContactMode());
    }

    private function createCommande(string $statut): Commande
    {
        $commande = new Commande();
        $commande->setStatut($statut);

        return $commande;
    }
}
