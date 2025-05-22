<?php

namespace App\Api\Console\Object;

use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Entity\User;

class NewsletterListObject
{
    public UserRole $role;
    public NewsletterObject $newsletter;

    public function __construct(Newsletter $newsletter, User $user)
    {
        $this->role = $user->getRole();
        $this->newsletter = new NewsletterObject($newsletter);
    }

}
