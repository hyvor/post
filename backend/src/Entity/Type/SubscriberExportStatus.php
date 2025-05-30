<?php

namespace App\Entity\Type;

enum SubscriberExportStatus: string
{
    case PENDING = 'pending';

    case COMPLETED = 'completed';

    case FAILED = 'failed';
}