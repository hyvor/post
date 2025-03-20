<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Issue\SendTestInput;
use App\Api\Console\Input\Issue\UpdateIssueInput;
use App\Api\Console\Object\IssueObject;
use App\Api\Console\Object\SendObject;
use App\Entity\Issue;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\EmailTransportService;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\SendService;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Template\TemplateRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class IssueController extends AbstractController
{

    public function __construct(
        private IssueService $issueService,
        private SendService $sendService,
        private NewsletterListService $newsletterListService,
        private TemplateRenderer $templateRenderer,
        private EmailTransportService $emailTransportService
    )
    {
    }

    #[Route('/issues', methods: 'GET')]
    public function getIssues(Request $request, Project $project): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $issues = $this
            ->issueService
            ->getIssues($project, $limit, $offset)
            ->map(fn($subscriber) => new IssueObject($subscriber));

        return $this->json($issues);
    }

    #[Route('/issues', methods: 'POST')]
    public function createIssue(Project $project): JsonResponse
    {
        $issue = $this->issueService->createIssueDraft($project);

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}', methods: 'GET')]
    public function getById(Issue $issue): JsonResponse
    {
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

            if ($missingListIds !== null)
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");

            $updates->lists = $input->lists;
        }

        if ($input->hasProperty('from_email')) {
            // TODO: validate the from email once sending emails are set up
            $updates->fromEmail = $input->from_email;
        }

        if ($input->hasProperty('reply_to_email'))
            $updates->replyToEmail = $input->reply_to_email;

        if ($input->hasProperty('content'))
            $updates->content = $input->content;

        $issueUpdated = $this->issueService->updateIssue($issue, $updates);

        return $this->json(new IssueObject($issueUpdated));
    }

    #[Route ('/issues/{id}', methods: 'DELETE')]
    public function deleteIssue(Issue $issue): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT)
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        $this->issueService->deleteIssue($issue);
        return $this->json([]);
    }

    #[Route ('/issues/{id}/send', methods: 'POST')]
    public function sendIssue(Issue $issue, MessageBusInterface $bus): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT)
            throw new UnprocessableEntityHttpException("Issue is not a draft.");

        if ($issue->getSubject() === null || trim($issue->getSubject()) === '')
            throw new UnprocessableEntityHttpException("Subject cannot be empty.");

        if ($issue->getListIds() === [])
            throw new UnprocessableEntityHttpException("Issue must have at least one list.");

        if ($issue->getContent() === null)
            throw new UnprocessableEntityHttpException("Content cannot be empty.");

        $fromEmail = $issue->getFromEmail();
        // TODO: validate from email

        $subscribersCount = $this->sendService->getSendableSubscribersCount($issue);
        if ($subscribersCount == 0)
            throw new UnprocessableEntityHttpException("No subscribers to send to.");


        $updates = new UpdateIssueDto();
        $updates->status = IssueStatus::SENDING;
        $updates->sendingAt = new \DateTimeImmutable();
        $updates->html = $this->sendService->renderHtml($issue);
        $updates->text = $this->sendService->renderText($issue);
        $updates->totalSends = $subscribersCount;
        $issue = $this->issueService->updateIssue($issue, $updates);

        $bus->dispatch(new SendIssueMessage($issue->getId()));

        return $this->json(new IssueObject($issue));
    }

    #[Route ('/issues/{id}/test', methods: 'POST')]
    public function sendTest(
        Request $request,
        Project $project,
        Issue $issue,
        #[MapRequestPayload] SendTestInput $input
    ): JsonResponse
    {
        // $content = $templateService->renderIssue($issue, $send);

        $this->emailTransportService->send(
            $input->email,
            (string) $issue->getSubject(),
            '<p>See Twig integration for better HTML integration!</p>'
        );

        return $this->json([]);
    }

    #[Route ('/issues/{id}/preview', methods: 'GET')]
    public function previewIssue(Project $project, Issue $issue): JsonResponse
    {
        $preview = $this->templateRenderer->renderFromIssue($project, $issue);
        return $this->json(['html' => $preview]);
    }

    #[Route ('/issues/{id}/progress', methods: 'GET')]
    public function getIssueProgress(Project $project, Issue $issue): JsonResponse
    {
        $progress = $this->sendService->getIssueProgress($issue);
        return $this->json($progress);
    }

    #[Route ('/issues/{id}/sends', methods: 'GET')]
    public function getIssueSends(Request $request, Issue $issue): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $sends = $this
            ->sendService
            ->getSends($issue, $limit, $offset)
            ->map(fn($send) => new SendObject($send));

        return $this->json($sends);
    }

    #[Route ('/issues/{id}/report', methods: 'GET')]
    public function getIssueReport(Issue $issue): JsonResponse
    {
        $counts = $this->issueService->getIssueCounts($issue);
        return $this->json(
            [
                'counts' => $counts
            ]
        );
    }
}
