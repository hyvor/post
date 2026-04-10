<?php

namespace App\Service\Sudo;

use Hyvor\Internal\Sudo\SudoRoleInterface;

enum SudoRole: string implements SudoRoleInterface
{
    // first role must always be sudo, who has access to everything
    case SUDO = 'sudo';

    /**
     * @return SudoPermission[]
     */
    public function getPermissions(): array
    {
        return match ($this) {
            self::SUDO => SudoPermission::cases(), // all
        };
    }
}
