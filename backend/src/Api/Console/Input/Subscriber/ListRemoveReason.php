<?php

namespace App\Api\Console\Input\Subscriber;

enum ListRemoveReason: string
{
    case UNSUBSCRIBE = 'unsubscribe';
    case OTHER = 'other';
}
