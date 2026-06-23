<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Service\CommandeHistoriqueService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommandeHistoriqueFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var array<string, list<string>>
     */
    private const STATUS_CHAINS = [
        'en_attente' => ['en_attente'],
        'accepte' => ['en_attente', 'accepte'],
        'en_preparation' => ['en_attente', 'accepte', 'en_preparation'],
        'en_cours_de_livraison' => ['en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison'],
        'livre' => ['en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison', 'livre'],
        'en_attente_retour_materiel' => ['en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'en_attente_retour_materiel'],
        'terminee' => ['en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'en_attente_retour_materiel', 'terminee'],
        'annulee' => ['en_attente', 'annulee'],
    ];

    public function load(ObjectManager $manager): void
    {
        $historiqueService = new CommandeHistoriqueService($manager);

        $commandeReferences = [
            'commande_0001', 'commande_0002', 'commande_0003', 'commande_0004',
            'commande_0005', 'commande_0006', 'commande_0007', 'commande_0008',
            'commande_0009', 'commande_0010',
        ];

        foreach ($commandeReferences as $reference) {
            /** @var Commande $commande */
            $commande = $this->getReference($reference, Commande::class);
            $statut = (string) $commande->getStatut();
            $chain = self::STATUS_CHAINS[$statut] ?? ['en_attente'];
            $baseDate = $commande->getDateCommande() ?? new \DateTimeImmutable();

            foreach ($chain as $index => $step) {
                $date = $baseDate->modify(sprintf('+%d hours', $index * 6));
                $historiqueService->record($commande, $step, $date);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CommandeFixtures::class];
    }
}
