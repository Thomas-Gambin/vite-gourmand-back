<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Avis;
use App\Service\AvisModerationService;
use App\Service\AvisStatus;
use PHPUnit\Framework\TestCase;

final class AvisModerationServiceTest extends TestCase
{
    public function testValiderAvisEnAttente(): void
    {
        $avis = new Avis();
        $avis->setStatut(AvisStatus::EN_ATTENTE);

        $service = new AvisModerationService();
        $service->valider($avis);

        self::assertSame(AvisStatus::VALIDE, $avis->getStatut());
    }

    public function testRefuserAvisEnAttente(): void
    {
        $avis = new Avis();
        $avis->setStatut(AvisStatus::EN_ATTENTE);

        $service = new AvisModerationService();
        $service->refuser($avis);

        self::assertSame(AvisStatus::REFUSE, $avis->getStatut());
    }

    public function testModerationRefuseAvisDejaValide(): void
    {
        $avis = new Avis();
        $avis->setStatut(AvisStatus::VALIDE);

        $service = new AvisModerationService();

        $this->expectException(\InvalidArgumentException::class);
        $service->valider($avis);
    }
}
