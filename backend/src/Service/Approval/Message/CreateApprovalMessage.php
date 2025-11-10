<?php

namespace App\Service\Approval\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class CreateApprovalMessage
{
    public function __construct(
        private int $approvalId
    )
    {
    }

    public function getApprovalId(): int
    {
        return $this->approvalId;
    }
}