<?php

namespace App\Enum;

enum SubscriberStatus: string
{
    case SUBSCRIBED = 'subscribed';
    case UNSUBSCRIBED = 'unsubscribed';
    case PENDING = 'pending';
}
