<?php

namespace App\Api\Console\Input\Subscriber;

enum CreateSubscriberIfExists: string {

    case ERROR = 'error';
    case UPDATE = 'update';

}
