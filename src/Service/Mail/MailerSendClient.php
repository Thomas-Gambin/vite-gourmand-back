<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MailerSendClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    /**
     * @param array<string,mixed> $payload
     */
    public function send(array $payload): void
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
}
