<?php

namespace App\Service\NotificationMail;

use App\Service\AppConfig;
use App\Service\Integration\Relay\RelayApiClient;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Service to send system notification emails like domain verification, etc.
 */
class NotificationMailService
{
    public function __construct(
        private RelayApiClient $relayApiClient,
        private AppConfig      $appConfig,
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
            ->from(new Address(
                address: $this->appConfig->getNotificationMailFromAddress(),
                name: $this->appConfig->getNotificationMailFromName()
            ))
            ->replyTo($this->appConfig->getNotificationMailReplyTo())
            ->to($emailAddress)
            ->subject($subject)
            ->html($content);

        $this->relayApiClient->sendEmail($email, isSystemNotification: true);
    }
}
