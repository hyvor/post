<?php

namespace App\Entity\Type;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
}
