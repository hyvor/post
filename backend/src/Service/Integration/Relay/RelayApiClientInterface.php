<?php

namespace App\Service\Integration\Relay;

use App\Service\Integration\Relay\Response\SendEmailResponse;
use Symfony\Component\Mime\Email;

interface RelayApiClientInterface
{

    public function sendEmail(
        Email   $email,
        ?string $idempotencyKey = null,
        bool    $isSystemNotification = false
    ): SendEmailResponse;
}
