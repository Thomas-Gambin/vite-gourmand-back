<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class PasswordResetEmailService
{
    public function __construct(
        private readonly MailerSendClient $client,
        private readonly Environment $twig,
        #[Autowire('%env(MAIL_FROM)%')]
        private readonly string $fromEmail,
        #[Autowire('%env(FRONTEND_URL)%')]
        private readonly string $frontendUrl,
    ) {}

    public function send(string $toEmail, string $prenom, string $plainToken): void
    {
        $base = rtrim($this->frontendUrl, '/');
        $resetUrl = $base.'/reset-password?token='.rawurlencode($plainToken);

        $html = $this->twig->render('emails/password_reset.html.twig', [
            'prenom' => $prenom,
            'resetUrl' => $resetUrl,
        ]);

        $this->client->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'Vite & Gourmand'],
            'to' => [['email' => $toEmail]],
            'subject' => 'Réinitialisez votre mot de passe Vite & Gourmand',
            'html' => $html,
        ]);
    }
}
