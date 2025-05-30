<?php

namespace App\Service\Template;

use App\Entity\Send;
use App\Service\Content\ContentService;
use App\Entity\Issue;
use Twig\Environment;

class HtmlTemplateRenderer
{

    public function __construct(
        private Environment $twig,
        private ContentService $contentService,
        private TemplateService $emailTemplateService,
    ) {
    }

    private function variablesFromIssue(Issue $issue): TemplateVariables
    {
        $variables = TemplateVariables::fromNewsletter($issue->getNewsletter());
        $variables->subject = (string)$issue->getSubject();
        $variables->content = $this->contentService->getHtmlFromJson(
            $issue->getContent() ?? ContentService::DEFAULT_CONTENT
        );
        return $variables;
    }

    /**
     * $issue needs newsletter, content, and subject at least.
     */
    public function renderFromIssue(Issue $issue): string
    {
        $newsletter = $issue->getNewsletter();
        $variables = $this->variablesFromIssue($issue);
        $template = $this->emailTemplateService->getTemplateStringFromNewsletter($newsletter);

        return $this->render($template, $variables);
    }

    private function variablesFromSend(Send $send): TemplateVariables
    {
        $issue = $send->getIssue();
        $variables = $this->variablesFromIssue($issue);
        // TODO: unsubscribe URL
        $variables->unsubscribe_url = 'https://post.hyvor.com/mypage/unsubscribe/' . $send->getId();
        return $variables;
    }

    public function renderFromSend(Send $send): string
    {
        $variables = $this->variablesFromSend($send);
        $template = $this->emailTemplateService->getTemplateStringFromNewsletter($send->getIssue()->getNewsletter());

        return $this->render($template, $variables);
    }

    public function render(string $template, TemplateVariables $variables): string
    {
        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }

}
