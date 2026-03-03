<?php

namespace App\Api\Console\Input\Subscriber;

enum ListSkipResubscribeOn: string
{
    case UNSUBSCRIBE = 'unsubscribe';
    case BOUNCE = 'bounce';
    case OTHER = 'other';
}
