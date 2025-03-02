<?php

namespace App\Entity\Factory;

use App\Entity\Project;

/**
 * @extends FactoryAbstract<Project>
 * @deprecated
 */
class ProjectFactory extends FactoryAbstract
{

    public function define() : Project
    {
        $project = new Project();
        $project->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $project->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $project->setUserId(1);
        $project->setName($this->fake->name());
        return $project;
    }

}
