<?php

namespace App\Service\Integration\Relay;

use Symfony\Component\Mime\Email;
use App\Service\Integration\Relay\Response\SendEmailResponse;
use Symfony\Component\Mailer\MailerInterface;

class RelayDevClient implements RelayApiClientInterface
{

    public function __construct(
        private MailerInterface $mailer,
    ) {}

    public function sendEmail(
        Email $email,
        ?string $idempotencyKey = null,
        bool $isSystemNotification = false
    ): SendEmailResponse {

        $this->mailer->send($email);

        $response = new SendEmailResponse();
        $response->id = 0;
        $response->message_id = '0';

        return $response;
    }
}
