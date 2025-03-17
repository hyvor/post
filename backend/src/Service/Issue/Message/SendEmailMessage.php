<?php

namespace App\Service\Issue\Message;

use App\Entity\Issue;
use App\Entity\Subscriber;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SendEmailMessage
{

    public function __construct(
        private int $sendId,
        private int $attempt = 1
    )
    {
    }

    public function getSendId(): int
    {
        return $this->sendId;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
