<?php

namespace App\Api\Console\Input\Subscriber;

enum RemoveSubscriberListReason: string
{
    case UNSUBSCRIBE = 'unsubscribe';
    case OTHER = 'other';
}
