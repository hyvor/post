<?php

namespace App\Api\Console\Resolver;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProjectResolver implements ValueResolverInterface
{

    public function __construct(
        private ProjectRepository $projectRepository,
        private Security $security
    )
    {
    }

    /**
     * @return iterable<Project>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {

        $argumentType = $argument->getType();

        if (
            !$argumentType ||
            $argumentType !== Project::class
        )
        {
            return [];
        }

        $controllerName = $argument->getControllerName();

        if (!str_starts_with($controllerName, 'App\Api\Console\Controller\\')) {
            return [];
        }

        $projectId = $request->headers->get('X-Resource-Id');

        if (!$projectId) {
            throw new BadRequestException('Missing X-Project-Id header');
        }

        $project = $this->projectRepository->find($projectId);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        // TODO: enable this after auth fake
        /*$user = $this->security->getUser();

        if (!$user instanceof AuthUser) {
            throw new AccessDeniedException('User not authenticated');
        }

        if ($project->getUserId() !== $user->id) {
            throw new AccessDeniedException('Project does not belong to the user');
        }*/

        // TODO: check roles

        return [$project];

    }


}