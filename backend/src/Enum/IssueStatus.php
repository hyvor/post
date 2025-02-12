<?php

namespace App\Enum;

enum IssueStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case SENDING = 'sending';
    case FAILED = 'failed';
    case SENT = 'sent';
}
