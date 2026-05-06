<?php

namespace App\Api\Sudo\Controller;

use App\Api\Console\Object\SendObject;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use App\Service\Issue\SendService;
use App\Service\Sudo\SudoPermission;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateRenderException;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class IssueController extends AbstractController
{
    public function __construct(
        private IssueService $issueService,
        private SendService $sendService,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
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

    #[Route('/issues/{id}/preview', methods: ['GET'])]
    public function previewIssue(Issue $issue): Response
    {
        $stored = $issue->getHtml();
        if ($stored !== null && $stored !== '') {
            return new Response($stored, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        try {
            $html = $this->htmlTemplateRenderer->renderFromIssue($issue);
        } catch (TemplateRenderException $e) {
            $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            $html = "<!doctype html><html><body style=\"font-family:sans-serif;padding:24px;color:#b00\">"
                . "<h3>Could not render preview</h3><pre>{$message}</pre></body></html>";
        }

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/issues/{id}/sends', methods: ['GET'])]
    public function getIssueSends(Request $request, Issue $issue): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $search = null;
        if ($request->query->has('search')) {
            $search = $request->query->getString('search');
        }

        $sendType = $request->query->has('type') ? $request->query->getString('type') : null;

        $sends = $this
            ->sendService
            ->getSends($issue, $limit, $offset, $search, $sendType)
            ->map(fn($send) => new SendObject($send));

        return new JsonResponse($sends->toArray());
    }
}
