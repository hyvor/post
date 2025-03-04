<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Issue\UpdateIssueInput;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
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
        private NewsletterListService $newsletterListService,
    )
    {
    }

    #[Route('/issues', methods: 'POST')]
    public function createIssue(Project $project): JsonResponse
    {
        $issue = $this->issueService->createIssueDraft($project);

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}', methods: 'PATCH')]
    public function updateIssue(
        Issue $issue,
        Project $project,
        #[MapRequestPayload] UpdateIssueInput $input
    ): JsonResponse
    {
        $updates = new UpdateIssueDto();

        if ($input->hasProperty('subject'))
            $updates->subject = $input->subject;

        if ($input->hasProperty('from_name'))
            $updates->fromName = $input->from_name;

        if ($input->hasProperty('lists')) {
            $missingListIds = $this->newsletterListService->isListsAvailable($project, $input->lists);

            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }
        }

        if ($input->hasProperty('from_email'))
            $updates->fromEmail = $input->from_email;

        if ($input->hasProperty('reply_to_email'))
            $updates->replyToEmail = $input->reply_to_email;

        if ($input->hasProperty('content'))
            $updates->content = $input->content;

        $issueUpdated = $this->issueService->updateIssue($issue, $updates);

        return $this->json(new IssueObject($issueUpdated));
    }
}
