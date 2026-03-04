<?php

namespace App\Entity\Type;

enum ListRemovalReason: string
{
    case UNSUBSCRIBE = 'unsubscribe';
    case BOUNCE = 'bounce';
    case COMPLAINT = 'complaint';
    case OTHER = 'other';
}
