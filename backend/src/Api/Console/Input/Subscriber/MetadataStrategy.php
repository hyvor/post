<?php

namespace App\Api\Console\Input\Subscriber;

enum MetadataStrategy: string
{
    case OVERWRITE = 'overwrite';
    case MERGE = 'merge';
}
