<?php

namespace App\Message;

use App\Entity\Issue;

class SendEmailMessage
{
    public function __construct(
        Issue $issue
    ) {
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }
}
