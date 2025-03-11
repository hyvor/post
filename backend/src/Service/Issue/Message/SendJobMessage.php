<?php

namespace App\Service\Issue\Message;

use App\Entity\Issue;
use App\Entity\Subscriber;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendJobMessage
{
    public function __construct(
        private Issue $issue,
        private Subscriber $subscriber
    )
    {
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }
}
