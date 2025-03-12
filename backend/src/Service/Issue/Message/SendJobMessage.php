<?php

namespace App\Service\Issue\Message;

use App\Entity\Issue;
use App\Entity\Subscriber;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendJobMessage
{
    public function __construct(
        private int $issueId,
        private int $sendId
    )
    {
    }

    public function getIssueId(): int
    {
        return $this->issueId;
    }

    public function getSendId(): int
    {
        return $this->sendId;
    }
}
