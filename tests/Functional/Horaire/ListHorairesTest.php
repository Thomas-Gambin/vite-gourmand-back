<?php

declare(strict_types=1);

namespace App\Tests\Functional\Horaire;

use App\Entity\Horaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListHorairesTest extends WebTestCase
{
    public function testListReturnsOrderedHoraires(): void
    {
        $client = static::createClient();
        $this->seedHoraires($client);

        $client->request('GET', '/api/horaires');

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertCount(2, $data);
        self::assertSame('Lundi', $data[0]['day']);
        self::assertSame('Fermé', $data[0]['hours']);
        self::assertTrue($data[0]['isClosed']);
        self::assertSame('Mardi', $data[1]['day']);
        self::assertSame('09h00 - 18h00', $data[1]['hours']);
        self::assertFalse($data[1]['isClosed']);
    }

    private function seedHoraires(KernelBrowser $client): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQuery('DELETE FROM App\Entity\Horaire h')->execute();

        $lundi = new Horaire();
        $lundi->setJour('Lundi');
        $lundi->setHeureOuverture('Fermé');
        $lundi->setHeureFermeture('Fermé');
        $entityManager->persist($lundi);

        $mardi = new Horaire();
        $mardi->setJour('Mardi');
        $mardi->setHeureOuverture('09:00');
        $mardi->setHeureFermeture('18:00');
        $entityManager->persist($mardi);

        $entityManager->flush();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function json(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
