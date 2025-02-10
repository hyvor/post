<?php

namespace App\Http\ConsoleApi\Controller;


use App\Domain\Project\ProjectService;
use App\Http\ConsoleApi\Object\ProjectObject;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController
{
   public function createProject(Request $request, ProjectService $projectService, AccessAuthUser $user): JsonResponse
   {
       $request->validate([
           'name'=>'required|string',
       ]);
       $name = (string) $request->string('name');

       $project = $projectService->createProject($user->id, $name);

      return response()->json(new ProjectObject($project));
   }
}
