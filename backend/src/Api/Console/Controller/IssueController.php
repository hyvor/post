<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
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
use App\Service\Template\TextTemplateRenderer;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\PostLicense;
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
        private IssueService          $issueService,
        private SendService           $sendService,
        private NewsletterListService $newsletterListService,
        private TextTemplateRenderer  $textTemplateRenderer,
        private HtmlTemplateRenderer  $htmlTemplateRenderer,
        private BillingInterface      $billing,
        private DomainService         $domainService,
        private UserService           $userService,
        private AuthInterface         $authService,
        private SendingProfileService $sendingProfileService
    )
    {
    }

    #[Route('/issues', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
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
    #[ScopeRequired(Scope::ISSUES_WRITE)]
    public function createIssue(Newsletter $newsletter): JsonResponse
    {
        $issue = $this->issueService->createIssueDraft($newsletter);

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
    public function getById(Issue $issue): JsonResponse
    {
        return $this->json(new IssueObject(
            $issue,
            $this->sendService->getSendableSubscribersCount($issue)
        ));
    }

    #[Route('/issues/{id}', methods: 'PATCH')]
    #[ScopeRequired(Scope::ISSUES_WRITE)]
    public function updateIssue(
        Issue                                 $issue,
        Newsletter                            $newsletter,
        #[MapRequestPayload] UpdateIssueInput $input
    ): JsonResponse
    {
        $updates = new UpdateIssueDto();

        if ($input->hasProperty('subject')) {
            $updates->subject = $input->subject;
        }

        if ($input->hasProperty('content')) {
            $updates->content = $input->content;
        }

        if ($input->hasProperty('sending_profile_id')) {
            $sendingProfile = $this->sendingProfileService->getSendingProfileOfNewsletterById(
                $newsletter,
                $input->sending_profile_id
            );

            if ($sendingProfile === null) {
                throw new UnprocessableEntityHttpException("Sending profile not found.");
            }

            $updates->sendingProfile = $sendingProfile;
        }

        if ($input->hasProperty('lists')) {
            $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter($newsletter, $input->lists);

            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $updates->lists = $input->lists;
        }

        $issueUpdated = $this->issueService->updateIssue($issue, $updates);

        return $this->json(new IssueObject(
            $issueUpdated,
            $this->sendService->getSendableSubscribersCount($issue)
        ));
    }

    #[Route ('/issues/{id}', methods: 'DELETE')]
    #[ScopeRequired(Scope::ISSUES_WRITE)]
    public function deleteIssue(Issue $issue): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT) {
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        }
        $this->issueService->deleteIssue($issue);
        return $this->json([]);
    }

    #[Route ('/issues/{id}/send', methods: 'POST')]
    #[ScopeRequired(Scope::ISSUES_WRITE)]
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

        $license = $this->billing->license($issue->getNewsletter()->getUserId(), $issue->getNewsletter()->getId());
        if (!$license instanceof PostLicense) {
            throw new UnprocessableEntityHttpException("License not found or invalid.");
        }

        $sendCountThisMonth = $this->sendService->getSendsCountThisMonthOfNewsletter($issue->getNewsletter());
        if ($sendCountThisMonth + $subscribersCount >= $license->emails)
            return $this->json([
                'message' => 'would_exceed_limit',
                'data' => [
                    'limit' => $license->emails,
                    'exceed_amount' => abs($license->emails - $sendCountThisMonth - $subscribersCount)
                ]
            ], 422);

        $updates = new UpdateIssueDto();
        $updates->status = IssueStatus::SENDING;
        $updates->sendingAt = new \DateTimeImmutable();
        $updates->html = $this->htmlTemplateRenderer->renderFromIssue($issue);
        $updates->text = $this->textTemplateRenderer->renderFromIssue($issue);
        $updates->totalSends = $subscribersCount;
        $updates->sendingProfile = $issue->getSendingProfile();

        $issue = $this->issueService->updateIssue($issue, $updates);

        $bus->dispatch(new SendIssueMessage($issue->getId()));

        return $this->json(new IssueObject($issue));
    }

    #[Route('/issues/{id}/test', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_WRITE)]
    public function getTestData(Issue $issue): JsonResponse
    {
        $newsletter = $issue->getNewsletter();
        $verifiedDomains = $this->domainService->getVerifiedDomainsByUserId($newsletter->getUserId());

        $newsletterUserIds = array_map(fn($user) => $user->getHyvorUserId(), $this->userService->getNewsletterUsers($newsletter)->toArray());
        $newsletterUserEmails = array_map(fn($authUser) => $authUser->email, $this->authService->fromIds($newsletterUserIds));

        $testSentEmails = $newsletter->getTestSentEmails() ?? [];
        $suggestedEmails = array_merge($newsletterUserEmails, $testSentEmails);

        return $this->json([
            'verified_domains' => array_map(fn($domain) => $domain->getDomain(), $verifiedDomains),
            'suggested_emails' => $suggestedEmails,
            'test_sent_emails' => $testSentEmails,
        ]);
    }

    #[Route ('/issues/{id}/test', methods: 'POST')]
    #[ScopeRequired(Scope::ISSUES_WRITE)]
    public function sendTest(
        Issue                              $issue,
        #[MapRequestPayload] SendTestInput $input
    ): JsonResponse
    {
        if ($issue->getStatus() != IssueStatus::DRAFT) {
            throw new UnprocessableEntityHttpException("Issue is not a draft.");
        }

        if ($issue->getSubject() === null || trim($issue->getSubject()) === '') {
            throw new UnprocessableEntityHttpException("Subject cannot be empty.");
        }

        if ($issue->getContent() === null) {
            throw new UnprocessableEntityHttpException("Content cannot be empty.");
        }

        $sendCount = $this->issueService->sendTestEmails($issue, $input->emails);

        return $this->json([
            'success_count' => $sendCount,
        ]);
    }

    #[Route ('/issues/{id}/preview', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
    public function previewIssue(Issue $issue): JsonResponse
    {
        $preview = $this->htmlTemplateRenderer->renderFromIssue($issue);

        return $this->json([
            'html' => $preview,
            'sendable_subscribers_count' => $this->sendService->getSendableSubscribersCount($issue)
        ]);
    }

    #[Route ('/issues/{id}/progress', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
    public function getIssueProgress(Newsletter $newsletter, Issue $issue): JsonResponse
    {
        $progress = $this->sendService->getIssueProgress($issue);
        return $this->json($progress);
    }

    #[Route ('/issues/{id}/sends', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
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

    #[Route ('/issues/{id}/report', methods: 'GET')]
    #[ScopeRequired(Scope::ISSUES_READ)]
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
