<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\Issue\SendTestInput;
use App\Api\Console\Input\Issue\UpdateIssueInput;
use App\Api\Console\Object\IssueObject;
use App\Api\Console\Object\SendObject;
use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Type\IssueStatus;
use App\Service\Domain\DomainService;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\SendService;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateRenderException;
use App\Service\Template\TextTemplateRenderer;
use App\Service\User\UserService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class IssueController extends AbstractController
{

    public function __construct(
        private IssueService $issueService,
        private SendService $sendService,
        private NewsletterListService $newsletterListService,
        private TextTemplateRenderer $textTemplateRenderer,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
        private BillingInterface $billing,
        private DomainService $domainService,
        private UserService $userService,
        private SendingProfileService $sendingProfileService,
    ) {}

    #[Route('/issues', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Get issues of the newsletter, paginated and ordered by most recently created.',
        summary: 'Get issues',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of issues',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: IssueObject::class)),
        ),
    )]
    public function getIssues(Request $request, Newsletter $newsletter): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $issues = $this
            ->issueService
            ->getIssues($newsletter, limit: $limit, offset: $offset)
            ->map(fn($subscriber) => new IssueObject($subscriber));

        return $this->json($issues);
    }

    #[Route('/issues', methods: 'POST')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Post(
        description: 'Creates a new draft issue for the newsletter.',
        summary: 'Create an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created issue object.',
        content: new Model(type: IssueObject::class),
    )]
    public function createIssue(Newsletter $newsletter): JsonResponse
    {
        $issue = $this->issueService->createIssueDraft($newsletter);

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Get an issue by ID.',
        summary: 'Get an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the issue object.',
        content: new Model(type: IssueObject::class),
    )]
    public function getById(Issue $issue): JsonResponse
    {
        return $this->json(
            new IssueObject(
                $issue,
                $this->sendService->getSendableSubscribersCount($issue),
            ),
        );
    }

    #[Route('/issues/{id}', methods: 'PATCH')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Patch(
        description: 'Updates a draft issue: its subject, content, sending profile, or lists.',
        summary: 'Update an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated issue object.',
        content: new Model(type: IssueObject::class),
    )]
    public function updateIssue(
        Issue $issue,
        Newsletter $newsletter,
        #[MapRequestPayload] UpdateIssueInput $input,
    ): JsonResponse {
        $updates = new UpdateIssueDto();

        if ($input->has('subject')) {
            $updates->subject = $input->subject;
        }

        if ($input->has('content')) {
            $updates->content = $input->content;
        }

        if ($input->has('sending_profile_id')) {
            $sendingProfile = $this->sendingProfileService->getSendingProfileOfNewsletterById(
                $newsletter,
                $input->sending_profile_id,
            );

            if ($sendingProfile === null) {
                throw new UnprocessableEntityHttpException("Sending profile not found.");
            }

            $updates->sendingProfile = $sendingProfile;
        }

        if ($input->has('lists')) {
            $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter($newsletter, $input->lists);

            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $updates->lists = $input->lists;
        }

        $issueUpdated = $this->issueService->updateIssue($issue, $updates);

        return $this->json(
            new IssueObject(
                $issueUpdated,
                $this->sendService->getSendableSubscribersCount($issue),
            ),
        );
    }

    #[Route('/issues/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Delete(
        description: 'Deletes a draft issue. Issues that are not in draft status cannot be deleted.',
        summary: 'Delete an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function deleteIssue(Issue $issue): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT) {
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        }
        $this->issueService->deleteIssue($issue);
        return $this->json([]);
    }

    #[Route('/issues/{id}/send', methods: 'POST')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Post(
        description: 'Sends a draft issue to its subscribers. Validates the issue, license, and monthly email ' .
            'limits before queuing the send.',
        summary: 'Send an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated issue object, now in sending status.',
        content: new Model(type: IssueObject::class),
    )]
    #[OA\Response(
        response: 422,
        description: 'Returned when sending the issue would exceed the organization\'s monthly email limit.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'would_exceed_limit'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'limit', type: 'integer'),
                        new OA\Property(property: 'exceed_amount', type: 'integer'),
                    ],
                    type: 'object',
                ),
            ],
        ),
    )]
    public function sendIssue(Issue $issue, MessageBusInterface $bus): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT) {
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        }

        if ($issue->getSubject() === null || trim($issue->getSubject()) === '') {
            throw new UnprocessableEntityHttpException("Subject cannot be empty.");
        }

        if ($issue->getListIds() === []) {
            throw new UnprocessableEntityHttpException("Issue must have at least one list.");
        }

        if ($issue->getContent() === null) {
            throw new UnprocessableEntityHttpException("Content cannot be empty.");
        }

        $subscribersCount = $this->sendService->getSendableSubscribersCount($issue);
        if ($subscribersCount == 0) {
            throw new UnprocessableEntityHttpException("No subscribers to send to.");
        }

        $organizationId = $issue->getNewsletter()->getOrganizationId();

        $resolvedLicense = $this->billing->license($organizationId);
        $license = $resolvedLicense->license;
        if (!$license instanceof PostLicense) {
            throw new UnprocessableEntityHttpException("License not found or invalid.");
        }

        if ($resolvedLicense->type === ResolvedLicenseType::TRIAL) {
            throw new UnprocessableEntityHttpException(
                "Cannot send issues during trial. Please upgrade your subscription.",
            );
        }

        $sendCountThisMonth = $this->sendService->getSendsCountThisMonthOfOrganization($organizationId);
        if ($sendCountThisMonth + $subscribersCount >= $license->emails) {
            return $this->json([
                'message' => 'would_exceed_limit',
                'data' => [
                    'limit' => $license->emails,
                    'exceed_amount' => abs($license->emails - $sendCountThisMonth - $subscribersCount),
                ],
            ], 422);
        }

        $updates = new UpdateIssueDto();
        $updates->status = IssueStatus::SENDING;
        $updates->sendingAt = new \DateTimeImmutable();
        try {
            $updates->html = $this->htmlTemplateRenderer->renderFromIssue($issue);
        } catch (TemplateRenderException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
        $updates->text = $this->textTemplateRenderer->renderFromIssue($issue);
        $updates->totalSendable = $subscribersCount;
        $updates->sendingProfile = $issue->getSendingProfile();

        $issue = $this->issueService->updateIssue($issue, $updates);

        $bus->dispatch(new SendIssueMessage($issue->getId()));

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}/test', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Get(
        description: 'Get data useful for sending a test email of an issue: verified domains, and suggested and ' .
            'previously used test email addresses.',
        summary: 'Get issue test data',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the test email data.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'verified_domains', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'suggested_emails', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'test_sent_emails', type: 'array', items: new OA\Items(type: 'string')),
            ],
        ),
    )]
    public function getTestData(Issue $issue): JsonResponse
    {
        $newsletter = $issue->getNewsletter();
        $verifiedDomains = $this->domainService->getVerifiedDomainsByOrganizationId($newsletter->getOrganizationId());
        $newsletterUserEmails = $this->userService->getNewsletterUserEmails($newsletter);

        $testSentEmails = $newsletter->getTestSentEmails() ?? [];
        $suggestedEmails = array_unique(array_merge($newsletterUserEmails, $testSentEmails));

        return $this->json([
            'verified_domains' => array_map(fn($domain) => $domain->getDomain(), $verifiedDomains),
            'suggested_emails' => $suggestedEmails,
            'test_sent_emails' => $testSentEmails,
        ]);
    }

    #[Route('/issues/{id}/test', methods: 'POST')]
    #[ScopeRequired(PostScope::ISSUES_WRITE)]
    #[OA\Post(
        description: 'Sends a test email of a draft issue to the given email addresses. Test emails can only be ' .
            'sent to verified domains or emails of newsletter users.',
        summary: 'Send a test issue email',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the number of test emails successfully sent.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success_count', type: 'integer'),
            ],
        ),
    )]
    public function sendTest(
        Issue $issue,
        #[MapRequestPayload] SendTestInput $input,
    ): JsonResponse {
        if ($issue->getStatus() != IssueStatus::DRAFT) {
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        }

        if ($issue->getSubject() === null || trim($issue->getSubject()) === '') {
            throw new UnprocessableEntityHttpException("Subject cannot be empty.");
        }

        if ($issue->getContent() === null) {
            throw new UnprocessableEntityHttpException("Content cannot be empty.");
        }

        if (!$this->issueService->isTestEmailAllowed($issue, $input->emails)) {
            throw new UnprocessableEntityHttpException(
                "Test emails can only be sent to verified domains or emails of newsletter users.",
            );
        }

        $sendCount = $this->issueService->sendTestEmails($issue, $input->emails);

        return $this->json([
            'success_count' => $sendCount,
        ]);
    }

    #[Route('/issues/{id}/preview', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Renders the HTML preview of an issue, and returns the number of subscribers it would be sendable to.',
        summary: 'Preview an issue',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the rendered HTML and sendable subscribers count.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'html', type: 'string'),
                new OA\Property(property: 'sendable_subscribers_count', type: 'integer'),
            ],
        ),
    )]
    public function previewIssue(Issue $issue): JsonResponse
    {
        try {
            $preview = $this->htmlTemplateRenderer->renderFromIssue($issue);
        } catch (TemplateRenderException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $this->json([
            'html' => $preview,
            'sendable_subscribers_count' => $this->sendService->getSendableSubscribersCount($issue),
        ]);
    }

    #[Route('/issues/{id}/progress', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Get the sending progress of an issue that is currently being sent.',
        summary: 'Get issue sending progress',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the issue sending progress.',
        content: new OA\JsonContent(type: 'object'),
    )]
    public function getIssueProgress(Newsletter $newsletter, Issue $issue): JsonResponse
    {
        $progress = $this->sendService->getIssueProgress($issue);
        return $this->json($progress);
    }

    #[Route('/issues/{id}/sends', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Get individual sends of an issue, paginated and optionally filtered by search term or send type.',
        summary: 'Get issue sends',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of sends',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SendObject::class)),
        ),
    )]
    public function getIssueSends(Request $request, Issue $issue): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $search = null;
        if ($request->query->has('search')) {
            $search = $request->query->getString('search');
        }

        $sendType = $request->query->getString('type');

        $sends = $this
            ->sendService
            ->getSends($issue, $limit, $offset, $search, $sendType)
            ->map(fn($send) => new SendObject($send));

        return $this->json($sends);
    }

    #[Route('/issues/{id}/report', methods: 'GET')]
    #[ScopeRequired(PostScope::ISSUES_READ)]
    #[OA\Get(
        description: 'Get the delivery, open, click, bounce, and complaint counts of an issue.',
        summary: 'Get issue report',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the issue report counts.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'counts', type: 'object'),
            ],
        ),
    )]
    public function getIssueReport(Issue $issue): JsonResponse
    {
        $counts = $this->sendService->getIssueStats($issue, full: true);
        return $this->json(
            [
                'counts' => $counts,
            ],
        );
    }
}
