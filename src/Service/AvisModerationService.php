<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Avis;
use InvalidArgumentException;

final class AvisModerationService
{
    public function valider(Avis $avis): void
    {
        $this->assertModeratable($avis);
        $avis->setStatut(AvisStatus::VALIDE);
    }

    public function refuser(Avis $avis): void
    {
        $this->assertModeratable($avis);
        $avis->setStatut(AvisStatus::REFUSE);
    }

    private function assertModeratable(Avis $avis): void
    {
        if (!AvisStatus::isModeratable((string) $avis->getStatut())) {
            throw new InvalidArgumentException('Seuls les avis en attente peuvent être modérés.');
        }
    }
}
