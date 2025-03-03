<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Issue\CreateIssueInput;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use App\Service\NewsletterList\NewsletterListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IssueController extends AbstractController
{

    public function __construct(
        private IssueService $issueService,
    )
    {
    }

    #[Route('/issues', methods: 'POST')]
    public function createIssue(Project $project): JsonResponse
    {
        $issue = $this->issueService->createIssueDraft($project);

        return $this->json(new IssueObject($issue));
    }
}
