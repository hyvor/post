<?php

namespace App\Entity\Type;

enum SubscriberSource: string
{
    case CONSOLE = 'console';
    case FORM = 'form';
    case IMPORT = 'import';
}
