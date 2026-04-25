<?php

namespace App\Api\Sudo\Controller;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class IssueController extends AbstractController
{
    public function __construct(
        private IssueService $issueService,
        private SudoObjectFactory $sudoObjectFactory,
    )
    {
    }

    #[Route('/issues', methods: ['GET'])]
    public function getIssues(Request $request): JsonResponse
    {
        $newsletterId = $request->query->has('newsletter_id') ? $request->query->getInt('newsletter_id') : null;
        $status = $request->query->has('status')
            ? IssueStatus::tryFrom($request->query->getString('status'))
            : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $relationships = [Issue::class => ['newsletter']];

        return new JsonResponse(
            array_map(
                fn($issue) => $this->sudoObjectFactory->create($issue, $relationships),
                $this->issueService->getIssuesGlobal($newsletterId, $status, $limit, $offset)
            )
        );
    }

    #[Route('/issues/{id}', methods: ['GET'])]
    public function getIssue(int $id): JsonResponse
    {
        $issue = $this->issueService->getIssueGlobal($id);

        if (!$issue) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $this->sudoObjectFactory->create($issue, [Issue::class => ['newsletter']])
        );
    }
}
