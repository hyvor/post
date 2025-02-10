<?php

namespace Tests\Feature\ConsoleApi\Project;
use App\Models\Project;
use Tests\Case\DatabaseTestCase;

class CreateProjectTest extends DatabaseTestCase
{
    public function testCreateProject(): void
    {
        $this->consoleUserApi('post', '/project', ['name' => 'Test Project'])
            ->assertOk()
            ->assertJsonPath('name', 'Test Project');

        $projects = Project::all();
        $this->assertCount(1, $projects);

        $project = $projects->first();
        $this->assertNotNull($project);
        $this->assertEquals('Test Project', $project->name);
    }
}
