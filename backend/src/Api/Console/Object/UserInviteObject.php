<?php

namespace App\Api\Console\Object;

use App\Entity\Type\UserRole;
use App\Entity\UserInvite;
use Hyvor\Internal\Auth\AuthUser;

class UserInviteObject
{
    public int $created_at;
    public UserRole $role;
    public UserMiniObject $user;
    public int $expires_at;

    public function __construct(
        UserInvite $userInvite, AuthUser $hyvorUser
    ) {
        $this->created_at = $userInvite->getCreatedAt()->getTimestamp();
        $this->role = UserRole::ADMIN; // Hardcoded for now
        $this->user = new UserMiniObject($hyvorUser);
        $this->expires_at = $userInvite->getExpiresAt()->getTimestamp();
    }
}
