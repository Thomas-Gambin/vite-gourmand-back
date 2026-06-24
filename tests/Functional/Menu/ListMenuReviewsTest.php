<?php

declare(strict_types=1);

namespace App\Tests\Functional\Menu;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Regime;
use App\Entity\Role;
use App\Entity\Theme;
use App\Entity\User;
use App\Service\AvisStatus;
use App\Service\CommandeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ListMenuReviewsTest extends WebTestCase
{
    public function testUnknownMenuReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/menus/999999/reviews');

        self::assertResponseStatusCodeSame(404);
    }

    public function testOnlyValidatedReviewsAreReturned(): void
    {
        $client = static::createClient();
        $menu = $this->createMenuWithReviews($client);

        $client->request('GET', sprintf('/api/menus/%d/reviews', $menu->getId()));

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertSame(1, $data['totalCount'] ?? null);
        self::assertSame(5, $data['averageRating'] ?? null);
        self::assertCount(1, $data['reviews'] ?? []);
        self::assertSame(5, $data['reviews'][0]['rating'] ?? null);
        self::assertSame('User Test', $data['reviews'][0]['author'] ?? null);
    }

    private function createMenuWithReviews(KernelBrowser $client): Menu
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $suffix = (string) random_int(100000, 999999);

        $theme = new Theme();
        $theme->setLibelle('Theme reviews '.$suffix);
        $entityManager->persist($theme);

        $regime = new Regime();
        $regime->setLibelle('Regime reviews '.$suffix);
        $entityManager->persist($regime);

        $menu = new Menu();
        $menu->setTitre('Menu reviews '.$suffix);
        $menu->setNombrePersonneMinimum(2);
        $menu->setPrixParPersonne('30.00');
        $menu->setDescription('Menu pour test avis publics.');
        $menu->setQuantiteRestante(5);
        $menu->setTheme($theme);
        $menu->setRegime($regime);
        $entityManager->persist($menu);

        $role = $entityManager->getRepository(Role::class)->findOneBy(['libelle' => 'ROLE_USER']);
        if (!$role instanceof Role) {
            $role = new Role();
            $role->setLibelle('ROLE_USER');
            $entityManager->persist($role);
        }

        $user = new User();
        $user->setEmail('reviews'.$suffix.'@ex.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setTelephone('0600000000');
        $user->setVille('Bordeaux');
        $user->setPays('France');
        $user->setAdressePostale('1 rue Test');
        $user->setRole($role);
        $user->setPassword($passwordHasher->hashPassword($user, 'Password123!'));
        $user->setIsVerified(true);
        $entityManager->persist($user);

        $commande = new Commande();
        $commande->setNumeroCommande('CMD-REV-'.$suffix);
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setDatePrestation(new \DateTimeImmutable('+7 days'));
        $commande->setHeureLivraison('12:00');
        $commande->setPrixMenu('100.00');
        $commande->setPrixLivraison('10.00');
        $commande->setNombrePersonne(4);
        $commande->setStatut(CommandeStatus::TERMINEE);
        $commande->setAdressePrestation('1 rue Test');
        $commande->setVillePrestation('Bordeaux');
        $commande->setCodePostalPrestation('33000');
        $commande->setUtilisateur($user);
        $commande->setMenu($menu);
        $entityManager->persist($commande);

        $validated = new Avis();
        $validated->setNote(5);
        $validated->setDescription('Excellent menu, je recommande vivement cette prestation.');
        $validated->setStatut(AvisStatus::VALIDE);
        $validated->setUtilisateur($user);
        $validated->setCommande($commande);
        $entityManager->persist($validated);

        $pending = new Avis();
        $pending->setNote(2);
        $pending->setDescription('Avis en attente qui ne doit pas apparaître.');
        $pending->setStatut(AvisStatus::EN_ATTENTE);
        $pending->setUtilisateur($user);
        $entityManager->persist($pending);

        $entityManager->flush();

        return $menu;
    }

    /**
     * @return array<string, mixed>
     */
    private function json(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
