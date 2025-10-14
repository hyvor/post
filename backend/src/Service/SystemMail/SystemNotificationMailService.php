<?php

namespace App\Service\SystemMail;

use App\Service\AppConfig;
use App\Service\Integration\Relay\RelayApiClient;
use Symfony\Component\Mime\Email;

/**
 * Service to send system notification emails like domain verification, etc.
 */
class SystemNotificationMailService
{

    public const string NOTIFICATIONS_MAIL_USERNAME = 'notifications';

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
        $fromEmail = self::NOTIFICATIONS_MAIL_USERNAME . '@' . $this->appConfig->getSystemMailDomain();

        $email = new Email()
            ->from($fromEmail)
            ->to($emailAddress)
            ->subject($subject)
            ->html($content);

        $replyTo = $this->appConfig->getSystemMailReplyTo();
        if ($replyTo) {
            $email->replyTo($replyTo);
        }

        $this->relayApiClient->sendEmail($email);
    }
}
