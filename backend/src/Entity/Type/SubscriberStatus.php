<?php

namespace App\Entity\Type;

enum SubscriberStatus: string
{
    case SUBSCRIBED = 'subscribed';
    case PENDING = 'pending';
}
