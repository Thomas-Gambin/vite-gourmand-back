<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Entity\Commande;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class OrderConfirmationEmailService
{
    public function __construct(
        private readonly MailerSendClient $client,
        private readonly Environment $twig,
        #[Autowire('%env(MAIL_FROM)%')]
        private readonly string $fromEmail,
    ) {
    }

    public function send(
        string $toEmail,
        string $prenom,
        Commande $commande,
        string $menuTitle,
        string $total,
    ): void {
        $html = $this->twig->render('emails/order_confirmation.html.twig', [
            'prenom' => $prenom,
            'numeroCommande' => $commande->getNumeroCommande(),
            'menuTitle' => $menuTitle,
            'datePrestation' => $commande->getDatePrestation()?->format('d/m/Y'),
            'heureLivraison' => $commande->getHeureLivraison(),
            'nombrePersonne' => $commande->getNombrePersonne(),
            'total' => $total,
            'statut' => $commande->getStatut(),
        ]);

        $this->client->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'Vite & Gourmand'],
            'to' => [['email' => $toEmail]],
            'subject' => sprintf('Confirmation de commande %s', $commande->getNumeroCommande()),
            'html' => $html,
        ]);
    }
}
