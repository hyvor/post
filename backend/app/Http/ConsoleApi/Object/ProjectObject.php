<?php

namespace App\Http\ConsoleApi\Object;

use App\Models\Project;

class ProjectObject
{
    public int $id;
    public int $created_at;
    public string $name;

    public function __construct(Project $project)
    {
        $this->id = $project->id;
        $this->created_at = $project->created_at->getTimestamp();
        $this->name = $project->name;
    }
}
