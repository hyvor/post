<?php

namespace App\Api\Public\Object\Form\Project;

use App\Entity\Project;

class FormProjectObject
{

    public string $uuid;
    public FormObject $form;

    public function __construct(Project $project)
    {
        $this->uuid = $project->getUuid();
        $meta = $project->getMeta();
        $this->form = new FormObject($meta);
    }

}