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

    public function send(string $toEmail, string $prenom, string $plainToken): void
    {
        $base = rtrim($this->frontendUrl, '/');
        $confirmUrl = $base.'/confirm-email?token='.rawurlencode($plainToken);

        $html = $this->twig->render('emails/confirm_registration.html.twig', [
            'prenom' => $prenom,
            'confirmUrl' => $confirmUrl,
        ]);

        $this->client->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'Vite & Gourmand'],
            'to' => [['email' => $toEmail]],
            'subject' => 'Confirmez votre inscription Vite & Gourmand',
            'html' => $html,
        ]);
    }
}
