<?php

namespace App\Api\Console\Input\Subscriber;

enum SubscriberListIfUnsubscribed: string
{
    case ERROR = 'error';
    case FORCE_CREATE = 'force_create';
}
