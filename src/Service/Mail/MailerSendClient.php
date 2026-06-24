<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MailerSendClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(MAILER_DSN)%')]
        private readonly string $mailerDsn,
        #[Autowire('%env(bool:USE_MAILPIT)%')]
        private readonly bool $useMailpit,
        private readonly string $apiKey,
    ) {}

    /**
     * @param array<string,mixed> $payload
     */
    public function send(array $payload): void
    {
        if ($this->shouldUseSymfonyMailer()) {
            $this->sendWithSymfonyMailer($payload);

            return;
        }

        try {
            $this->sendWithMailerSend($payload);
            $this->logger->info('Transactional email sent via MailerSend.', [
                'to' => $payload['to'][0]['email'] ?? null,
                'subject' => $payload['subject'] ?? null,
            ]);
        } catch (\Throwable $e) {
            if ($this->canFallbackToSymfonyMailer()) {
                $this->logger->warning('MailerSend failed, falling back to Symfony Mailer.', [
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'to' => $payload['to'][0]['email'] ?? null,
                ]);
                $this->sendWithSymfonyMailer($payload);

                return;
            }

            throw $e;
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendWithMailerSend(array $payload): void
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.mailersend.com/v1/email', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
                'json' => $payload,
                'timeout' => 8,
            ]);

            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300) {
                throw new \RuntimeException(sprintf('MailerSend API error (HTTP %d): %s', $status, $response->getContent(false)));
            }
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('MailerSend request failed (transport error).', previous: $e);
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendWithSymfonyMailer(array $payload): void
    {
        $fromEmail = (string) ($payload['from']['email'] ?? 'noreply@localhost');
        $fromName = (string) ($payload['from']['name'] ?? '');
        $toEmail = (string) ($payload['to'][0]['email'] ?? '');

        if ($toEmail === '') {
            throw new \InvalidArgumentException('Email recipient is missing.');
        }

        $email = (new Email())
            ->from('' !== $fromName ? new Address($fromEmail, $fromName) : $fromEmail)
            ->to($toEmail)
            ->subject((string) ($payload['subject'] ?? ''))
            ->html((string) ($payload['html'] ?? ''));

        if (isset($payload['text']) && is_string($payload['text']) && $payload['text'] !== '') {
            $email->text($payload['text']);
        }

        $this->mailer->send($email);

        $this->logger->info('Transactional email sent via Symfony Mailer.', [
            'to' => $toEmail,
            'subject' => $payload['subject'] ?? null,
            'transport' => $this->mailerDsn,
        ]);
    }

    private function shouldUseSymfonyMailer(): bool
    {
        return !$this->hasValidApiKey();
    }

    private function canFallbackToSymfonyMailer(): bool
    {
        return $this->hasValidApiKey()
            && $this->useMailpit
            && $this->mailerDsn !== 'null://null';
    }

    private function hasValidApiKey(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== 'test';
    }
}
