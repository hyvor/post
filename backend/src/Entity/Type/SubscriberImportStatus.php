<?php

namespace App\Entity\Type;

enum SubscriberImportStatus: string
{

    case REQUIRES_INPUT = 'requires_input';
    case PENDING_APPROVAL = 'pending_approval';
    case IMPORTING = 'importing';
    case FAILED = 'failed';
    case COMPLETED = 'completed';

}