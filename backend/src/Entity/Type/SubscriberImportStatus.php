<?php

namespace App\Entity\Type;

enum SubscriberImportStatus: string
{

    case REQUIRES_INPUT = 'requires_input';
    case IMPORTING = 'importing';
    case FAILED = 'failed';
    case COMPLETED = 'completed';

}