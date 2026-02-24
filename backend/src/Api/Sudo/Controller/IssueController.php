<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Object\SudoIssueObject;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class IssueController extends AbstractController
{
    public function __construct(
        private IssueService $issueService,
    )
    {
    }

    #[Route('/issues', methods: ['GET'])]
    public function getIssues(Request $request): JsonResponse
    {
        $subdomain = $request->query->has('subdomain') ? $request->query->getString('subdomain') : null;
        $status = $request->query->has('status')
            ? IssueStatus::tryFrom($request->query->getString('status'))
            : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        return new JsonResponse(
            array_map(
                fn($issue) => new SudoIssueObject($issue),
                $this->issueService->getIssuesGlobal($subdomain, $status, $limit, $offset)
            )
        );
    }

    #[Route('/issues/{id}', methods: ['GET'])]
    public function getIssue(Issue $issue): JsonResponse
    {
        return new JsonResponse(new SudoIssueObject($issue));
    }
}
