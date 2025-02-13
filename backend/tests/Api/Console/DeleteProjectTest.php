<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Service\Project\ProjectService;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ProjectController::class, 'deleteProject')]
#[CoversMethod(ProjectService::class, 'deleteProject')]
class DeleteProjectTest
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication

}