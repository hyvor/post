<?php

namespace App\Api\Console\Input\Subscriber;

enum ListsStrategy: string
{

    case SYNC = 'sync';
    case ADD = 'add';
    case REMOVE = 'remove';

}
