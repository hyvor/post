<?php

namespace App\Api\Console\Object;

use App\Entity\Type\UserRole;
use Hyvor\Internal\Auth\AuthUser;

class UserMiniObject
{
    public string $name;
    public ?string $username;
    public ?string $picture_url;

    public function __construct(
        AuthUser $hyvorUser
    ) {
        $this->name = $hyvorUser->name;
        $this->username = $hyvorUser->username;
        $this->picture_url = $hyvorUser->picture_url;
    }
}
