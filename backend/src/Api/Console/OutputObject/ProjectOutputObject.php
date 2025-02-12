<?php

namespace App\Api\Console\OutputObject;

use App\Entity\Project;

class ProjectOutputObject
{

    public int $id;
    public int $created_at; // unix timestamp

    public function __construct(Project $project)
    {
        $this->id = $project->getId();
        $this->created_at = $project->getCreatedAt()->getTimestamp();
    }

}