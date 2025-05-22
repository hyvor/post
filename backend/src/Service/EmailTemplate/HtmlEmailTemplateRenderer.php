<?php

namespace App\Service\EmailTemplate;

use App\Entity\Send;
use App\Service\Content\ContentService;
use App\Entity\Issue;
use Twig\Environment;

class HtmlEmailTemplateRenderer
{

    public function __construct(
        private Environment $twig,
        private ContentService $contentService,
        private EmailTemplateService $templateService,
    ) {
    }

    private function variablesFromIssue(Issue $issue): EmailTemplateVariables
    {
        $variables = EmailTemplateVariables::fromNewsletter($issue->getNewsletter());
        $variables->subject = (string)$issue->getSubject();
        $variables->content = $this->contentService->getHtmlFromJson(
            $issue->getContent() ?? ContentService::DEFAULT_CONTENT
        );
        return $variables;
    }

    public function renderFromIssue(Issue $issue): string
    {
        $newsletter = $issue->getNewsletter();
        $variables = $this->variablesFromIssue($issue);
        $template = $this->templateService->getTemplateStringFromNewsletter($newsletter);

        return $this->render($template, $variables);
    }

    private function variablesFromSend(Send $send): EmailTemplateVariables
    {
        $issue = $send->getIssue();
        $variables = $this->variablesFromIssue($issue);
        $variables->unsubscribe_url = 'https://post.hyvor.com/mypage/unsubscribe/' . $send->getId();
        return $variables;
    }

    public function renderFromSend(Send $send): string
    {
        $variables = $this->variablesFromSend($send);
        $template = $this->templateService->getTemplateStringFromNewsletter($send->getIssue()->getNewsletter());

        return $this->render($template, $variables);
    }

    public function render(string $template, EmailTemplateVariables $variables): string
    {
        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }

}
