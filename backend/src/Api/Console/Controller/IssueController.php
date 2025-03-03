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

        if ($input->hasProperty('subject')) {
            $updates->subject = $input->subject;
        }

        if ($input->hasProperty('from_name')) {
            $updates->fromName = $input->from_name;
        }

        if ($input->hasProperty('from_email')) {
            $updates->fromEmail = $input->from_email;
        }

        if ($input->hasProperty('reply_to_email')) {
            $updates->replyToEmail = $input->reply_to_email;
        }

        if ($input->hasProperty('content')) {
            $updates->content = $input->content;
        }

        if ($input->hasProperty('status')) {
            $updates->status = $input->status;
        }

        if ($input->hasProperty('html')) {
            $updates->html = $input->html;
        }

        if ($input->hasProperty('text')) {
            $updates->text = $input->text;
        }

        if ($input->hasProperty('error_private')) {
            $updates->errorPrivate = $input->error_private;
        }

        if ($input->hasProperty('batch_id')) {
            $updates->batchId = $input->batch_id;
        }

        if ($input->hasProperty('scheduled_at')) {
            $updates->scheduledAt = \DateTimeImmutable::createFromTimestamp($input->scheduled_at);
        }

        if ($input->hasProperty('sending_at')) {
            $updates->sendingAt = \DateTimeImmutable::createFromTimestamp($input->sending_at);
        }

        if ($input->hasProperty('failed_at')) {
            $updates->failedAt = \DateTimeImmutable::createFromTimestamp($input->failed_at);
        }

        if ($input->hasProperty('sent_at')) {
            $updates->sentAt = \DateTimeImmutable::createFromTimestamp($input->sent_at);
        }

        $issueUpdated = $this->issueService->updateIssue($issue, $updates);

        return $this->json(new IssueObject($issueUpdated));
    }
}
