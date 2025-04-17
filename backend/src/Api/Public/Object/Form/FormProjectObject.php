<?php

namespace App\Api\Public\Object\Form;

use App\Entity\Project;

class FormProjectObject
{

    public string $uuid;

    public function __construct(Project $project)
    {
        $this->uuid = $project->getUuid();
    }

}