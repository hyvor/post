<?php

namespace App\Entity\Type;

enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case REVIEWING = 'reviewing';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
