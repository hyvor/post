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
            try {
                $contentTemplate = $this->twig->createTemplate($variables->content);
                $variables->content = $contentTemplate->render((array)$variables);
            } catch (RuntimeError $e) {
                throw new UnprocessableEntityHttpException($this->formatTwigError($e));
            }
        }

        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }

    private function formatTwigError(RuntimeError $e): string
    {
        $message = $e->getRawMessage();

        if (preg_match('/Variable "([^"]+)" does not exist/', $message, $matches)) {
            return "Unknown Twig variable: {{ {$matches[1]} }}";
        }

        return "Twig error: $message";
    }
}
