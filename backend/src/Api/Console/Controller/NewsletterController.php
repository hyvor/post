<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Newsletter\CreateNewsletterInput;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInput;
use App\Api\Console\Object\NewsletterObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;

class NewsletterController extends AbstractController
{
    use HasHyvorUser;

    public function __construct(
        private NewsletterService $projectService
    ) {
    }

    #[Route('/newsletter', methods: 'POST')]
    public function createProject(#[MapRequestPayload] CreateNewsletterInput $input): JsonResponse
    {
        $user = $this->getHyvorUser();

        $slugger = new AsciiSlugger();
        while ($this->projectService->isUsernameTaken($slugger->slug($input->name))) {
            $input->name .= ' ' . random_int(1, 100);
        }

        $project = $this->projectService->createProject($user->id, $input->name);
        return $this->json(new NewsletterObject($project));
    }

    #[Route('/newsletter', methods: 'GET', condition: 'request.headers.get("X-Project-Id") !== null')]
    public function getProjectById(Newsletter $project): JsonResponse
    {
        return $this->json(new NewsletterObject($project));
    }

    #[Route('/newsletter', methods: 'DELETE')]
    public function deleteProject(Newsletter $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return $this->json([]);
    }

    #[Route('/newsletter', methods: 'PATCH')]
    public function updateProject(
        Newsletter $project,
        #[MapRequestPayload] UpdateNewsletterInput $input
    ): JsonResponse {
        $updates = new UpdateNewsletterDto();
        if ($input->hasProperty('name')) {
            $updates->name = $input->name;
        }
        if ($input->hasProperty('default_email_username')) {
            if ($this->projectService->isUsernameTaken($input->default_email_username)) {
                throw new BadRequestHttpException("Username is already taken");
            }
            $updates->defaultEmailUsername = $input->default_email_username;
        }
        $project = $this->projectService->updateProject($project, $updates);

        $updatesMeta = new UpdateNewsletterMetaDto();
        $properties = $input->getSetProperties();
        foreach ($properties as $property) {
            $cased = new UnicodeString($property)->camel();
            $updatesMeta->{$cased} = $input->{$property};
        }

        $project = $this->projectService->updateProjectMeta($project, $updatesMeta);

        return $this->json(new NewsletterObject($project));
    }
}
