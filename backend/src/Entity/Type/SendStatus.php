<?php

namespace App\Entity\Type;

enum SendStatus: string
{

    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';

}