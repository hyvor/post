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
        private NewsletterListService $newsletterListService,
    )
    {
    }

    #[Route('/issues', methods: 'POST')]
    public function createIssue(#[MapRequestPayload] CreateIssueInput $input, Project $project): JsonResponse
    {
        $list = $this->newsletterListService->getNewsletterList($input->list_id);

        if ($list === null) {
            throw new UnprocessableEntityHttpException("List with id {$input->list_id} not found");
        }

        $issue = $this->issueService->createIssue(
            $list,
            $input->subject,
            $input->from_name,
            $input->from_email,
            $input->reply_to_email,
            $input->content,
            $input->status ?? IssueStatus::DRAFT,
            $input->html,
            $input->text,
            $input->error_private,
            $input->batch_id,
            $input->scheduled_at,
            $input->sending_at,
            $input->failed_at,
            $input->sent_at
        );

        return $this->json(new IssueObject($issue));
    }
}
