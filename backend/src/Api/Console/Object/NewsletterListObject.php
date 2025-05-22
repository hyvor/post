<?php

namespace App\Api\Console\Object;

use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Entity\User;

class NewsletterListObject
{
    public UserRole $role;
    public NewsletterObject $project;

    public function __construct(Newsletter $project, User $user)
    {
        $this->role = $user->getRole();
        $this->project = new NewsletterObject($project);
    }

}
