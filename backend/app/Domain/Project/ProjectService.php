<?php

namespace App\Domain\Project;

use App\Models\Project;

class ProjectService
{
    public function createProject(int $userId, string $name): Project
    {
        $project = Project::create(
            [
                'user_id' => $userId,
                'name' => $name,
            ]
        );
        $project->refresh();
        return $project;
    }
}
