<?php

namespace App\Service\Template;

use App\Entity\Send;
use App\Entity\Issue;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Twig\Environment;
use Twig\Error\RuntimeError;

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
        if (!empty($variables->content)) {
            $template = str_replace('{{ content }}', $variables->content, $template);
        }
        try {
            $twigTemplate = $this->twig->createTemplate($template);
            return $twigTemplate->render((array)$variables);
        } catch (RuntimeError $e) {
            throw new UnprocessableEntityHttpException($e->getRawMessage());
        }
    }
}
