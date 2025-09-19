<?php

namespace App\Entity\Type;

enum RelayDomainStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case WARNING = 'warning';
    case SUSPENDED = 'suspended';
}
