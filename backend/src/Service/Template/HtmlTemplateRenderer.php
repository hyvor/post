<?php

namespace App\Service\Template;

use App\Entity\Send;
use App\Service\Content\ContentService;
use App\Entity\Issue;
use App\Service\Newsletter\NewsletterService;
use Hyvor\Internal\Util\Crypt\Encryption;
use Twig\Environment;

class HtmlTemplateRenderer
{

    public function __construct(
        private Environment             $twig,
        private TemplateService         $emailTemplateService,
        private TemplateVariableService $templateVariableService,
    )
    {
    }

    /**
     * $issue needs newsletter, content, and subject at least.
     */
    public function renderFromIssue(Issue $issue): string
    {
        $newsletter = $issue->getNewsletter();
        $variables = $this->templateVariableService->variablesFromIssue($issue);
        $template = $this->emailTemplateService->getTemplateStringFromNewsletter($newsletter);

        return $this->render($template, $variables);
    }

    public function renderFromSend(Send $send): string
    {
        $variables = $this->templateVariableService->variablesFromSend($send);
        $template = $this->emailTemplateService->getTemplateStringFromNewsletter($send->getIssue()->getNewsletter());

        return $this->render($template, $variables);
    }

    public function render(string $template, TemplateVariables $variables): string
    {
        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }
}
