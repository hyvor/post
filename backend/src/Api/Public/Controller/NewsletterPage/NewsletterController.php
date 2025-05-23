<?php

namespace App\Api\Public\Controller\NewsletterPage;

use App\Api\Public\Input\Newsletter\NewsletterInitInput;
use App\Api\Public\Object\NewsletterPage\IssueListObject;
use App\Api\Public\Object\NewsletterPage\NewsletterObject;
use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{

    const ISSUES_LIMIT = 25;

    public function __construct(
        private NewsletterService $newsletterService,
        private IssueService $issueService,
    ) {
    }

    #[Route('/newsletter-page/newsletter', methods: 'GET')]
    public function getNewsletter(
        #[MapQueryString] NewsletterInitInput $input,
    ): JsonResponse {
        $newsletter = $this->newsletterService->getNewsletterBySlug($input->slug);

        if ($newsletter === null) {
            throw new UnprocessableEntityHttpException('Newsletter not found');
        }

        return new JsonResponse([
            'newsletter' => new NewsletterObject($newsletter),
            'issues' => $this->getIssueListObjects($newsletter)
        ]);
    }

    /**
     * @return array<IssueListObject>
     */
    private function getIssueListObjects(Newsletter $newsletter, int $offset = 0): array
    {
        $issues = $this->issueService->getIssues(
            $newsletter,
            limit: self::ISSUES_LIMIT,
            offset: $offset,
            status: IssueStatus::SENT,
        );

        return $issues->map(fn(Issue $issue) => new IssueListObject($issue))->toArray();
    }

    #[Route('/newsletter-page/issues', methods: 'GET')]
    public function getIssues(
        #[MapQueryString] NewsletterInitInput $input,
    ): JsonResponse {
        //

        return new JsonResponse([
            //
        ]);
    }

    #[Route('/newsletter-page/issues/{uuid}', methods: 'GET')]
    public function getIssueHtml(string $uuid): JsonResponse
    {
        $issue = $this->issueService->getIssueByUuid($uuid);

        if ($issue === null) {
            throw new UnprocessableEntityHttpException('Issue not found');
        }

        if ($issue->getStatus() !== IssueStatus::SENT) {
            throw new UnprocessableEntityHttpException('Issue not sent');
        }

        $html = $issue->getHtml();

        return new JsonResponse([
            'subject' => $issue->getSubject(),
            'html' => $html,
        ]);
    }

}