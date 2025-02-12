<?php

namespace App\Entity\Factory;

use App\Entity\Project;

/**
 * @extends FactoryAbstract<Project>
 */
class ProjectFactory extends FactoryAbstract
{

    public function define()
    {

        $project = new Project();
        $project->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $project->setUserId(1);
        // TODO:

        return $project;

    }

}