<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class ConfirmRegistrationEmailService
{
    public function __construct(
        private readonly MailerSendClient $client,
        private readonly Environment $twig,
        #[Autowire('%env(MAIL_FROM)%')]
        private readonly string $fromEmail,
        #[Autowire('%env(FRONTEND_URL)%')]
        private readonly string $frontendUrl,
    ) {}

    public function send(string $toEmail, string $prenom, string $plainToken, bool $isEmailChange = false): void
    {
        $base = rtrim($this->frontendUrl, '/');
        $confirmUrl = $base.'/confirm-email?token='.rawurlencode($plainToken);

        $html = $this->twig->render('emails/confirm_registration.html.twig', [
            'prenom' => $prenom,
            'confirmUrl' => $confirmUrl,
            'isEmailChange' => $isEmailChange,
        ]);

        $subject = $isEmailChange
            ? 'Confirmez votre nouvelle adresse email — Vite & Gourmand'
            : 'Confirmez votre inscription Vite & Gourmand';

        $intro = $isEmailChange
            ? 'Vous avez modifié l’adresse email de votre compte Vite & Gourmand.'
            : 'Merci de vous être inscrit sur Vite & Gourmand.';

        $text = sprintf(
            "Bonjour %s,\n\n%s Pour activer votre compte, confirmez votre adresse email en ouvrant ce lien (valable 24 h) :\n\n%s\n",
            $prenom,
            $intro,
            $confirmUrl,
        );

        $this->client->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'Vite & Gourmand'],
            'to' => [['email' => $toEmail]],
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
        ]);
    }
}
