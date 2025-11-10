<?php

namespace App\Service\Template;

use App\Entity\Issue;
use App\Entity\Send;
use App\Service\Content\ContentService;
use function PHPUnit\Framework\assertNotNull;

class TextTemplateRenderer
{
    const string LINE_BREAK = "\n\n";

    public function __construct(
        private TemplateVariableService $templateVariableService,
        private ContentService          $contentService,
    )
    {
    }

    public function renderFromSend(Send $send): string
    {
        $variables = $this->templateVariableService->variablesFromSend($send);

        $text = $variables->name . self::LINE_BREAK;
        $text .= $variables->subject . self::LINE_BREAK;
        $text .= $this->contentService->getTextFromJson($send->getIssue()->getContent() ?? ContentService::DEFAULT_CONTENT) . self::LINE_BREAK;
        $text .= 'Unsubscribe (' . $variables->unsubscribe_url . ')';

        return $text;
    }

    public function renderFromIssue(Issue $issue): string
    {
        assertNotNull($issue->getContent());

        $variables = $this->templateVariableService->variablesFromIssue($issue);

        $text = $variables->name . self::LINE_BREAK;
        $text .= $variables->subject . self::LINE_BREAK;
        $text .= $this->contentService->getTextFromJson($issue->getContent());

        return $text;
    }
}
