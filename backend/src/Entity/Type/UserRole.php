<?php

namespace App\Entity\Type;

use Hyvor\Internal\CloudApi\Scope\PostScope;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';

    /**
     * @return PostScope[]
     */
    public function scopes(): array
    {
        return match ($this) {
            self::OWNER => PostScope::cases(),
            self::ADMIN => PostScope::cases(),
        };
    }
}
