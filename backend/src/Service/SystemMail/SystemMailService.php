<?php

namespace App\Service\SystemMail;

use App\Service\AppConfig;
use App\Service\Integration\Relay\RelayApiClient;
use Symfony\Component\Mime\Email;

/**
 * Service to send system emails like domain verification, etc.
 */
class SystemMailService
{
    public function __construct(
        private RelayApiClient $relayApiClient,
        private AppConfig $appConfig,
    )
    {
    }

    public function send(
        string $emailAddress,
        string $subject,
        string $content,
    ): void
    {
        $email = new Email()
            ->from($this->appConfig->getSystemMailFrom())
            ->to($emailAddress)
            ->subject($subject)
            ->html($content);

        $this->relayApiClient->sendEmail($email);
    }
}
