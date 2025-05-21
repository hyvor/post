<?php

namespace App\Api\Console\Object;

use App\Entity\Type\UserRole;
use App\Entity\User;
use Hyvor\Internal\Auth\AuthUser;

class UserObject
{
    public int $id;
    public UserRole $role;
    public int $created_at;
    public UserMiniObject $user;

    public function __construct(User $user, AuthUser $authUser)
    {
        $this->id = $user->getId();
        $this->role = $user->getRole();
        $this->created_at = $user->getCreatedAt()->getTimestamp();
        $this->user = new UserMiniObject($authUser);
    }
}
