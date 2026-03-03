<?php

namespace App\Api\Console\Input\Subscriber;

enum ListAddStrategyIfUnsubscribed: string
{
    case IGNORE = 'ignore';
    case FORCE_ADD = 'force_add';
}
