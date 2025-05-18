<?php

namespace App\Api\Console\Object;

use App\Entity\Project;
use App\Entity\Type\UserRole;
use App\Entity\User;

class ProjectListObject
{
    public UserRole $role;
    public ProjectObject $project;

    public function __construct(Project $project, User $user)
    {
        $this->role = $user->getRole();
        $this->project = new ProjectObject($project);
    }

}
