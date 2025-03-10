<?php

namespace App\Service\Issue\Message;

use App\Entity\Issue;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendEmailMessage
{
    public function __construct(
        private Issue $issue
    ) {
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }
}
