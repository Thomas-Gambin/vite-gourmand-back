<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\User;
use App\Service\CommandeAnnulationService;
use App\Service\CommandeHistoriqueService;
use App\Service\CommandeStatutService;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class CommandeAnnulationServiceTest extends TestCase
{
    public function testAnnulationEmployeExigeContact(): void
    {
        $service = new CommandeAnnulationService(
            new CommandeStatutService(new CommandeHistoriqueService($this->createMock(EntityManagerInterface::class))),
            $this->createMock(EntityManagerInterface::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->annuler(new Commande(), new User(), null, true);
    }

    public function testAnnulationClientSansContact(): void
    {
        $commande = new Commande();
        $commande->setStatut(CommandeStatus::EN_ATTENTE);
        $menu = new Menu();
        $menu->setQuantiteRestante(4);
        $commande->setMenu($menu);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $historiqueService = new CommandeHistoriqueService($entityManager);
        $service = new CommandeAnnulationService(new CommandeStatutService($historiqueService), $entityManager);

        $service->annuler($commande, new User(), null, false);

        self::assertSame(CommandeStatus::ANNULEE, $commande->getStatut());
        self::assertSame(5, $menu->getQuantiteRestante());
    }
}
