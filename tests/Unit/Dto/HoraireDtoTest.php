<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\Horaire\HoraireDto;
use App\Entity\Horaire;
use PHPUnit\Framework\TestCase;

final class HoraireDtoTest extends TestCase
{
    public function testFormatsOpenDay(): void
    {
        $horaire = new Horaire();
        $horaire->setJour('Mardi');
        $horaire->setHeureOuverture('09:00');
        $horaire->setHeureFermeture('18:00');

        $dto = HoraireDto::fromEntity($horaire);

        self::assertSame('Mardi', $dto->day);
        self::assertSame('09h00 - 18h00', $dto->hours);
        self::assertFalse($dto->isClosed);
    }

    public function testFormatsClosedDay(): void
    {
        $horaire = new Horaire();
        $horaire->setJour('Lundi');
        $horaire->setHeureOuverture('Fermé');
        $horaire->setHeureFermeture('Fermé');

        $dto = HoraireDto::fromEntity($horaire);

        self::assertSame('Fermé', $dto->hours);
        self::assertTrue($dto->isClosed);
    }
}
