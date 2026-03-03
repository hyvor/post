<?php

namespace App\Api\Console\Input\Subscriber;

enum ListsStrategy: string
{

    case MERGE = 'merge';
    case OVERWRITE = 'overwrite';
    case REMOVE = 'remove';

}
