<?php

namespace App\Service\Content;

use App\Entity\Issue;
use App\Service\Content\Marks\Code;
use App\Service\Content\Marks\Em;
use App\Service\Content\Marks\Link;
use App\Service\Content\Marks\Strike;
use App\Service\Content\Marks\Strong;
use App\Service\Content\Marks\Underline;
use App\Service\Content\Nodes\Blockquote;
use App\Service\Content\Nodes\Button;
use App\Service\Content\Nodes\Doc;
use App\Service\Content\Nodes\HardBreak;
use App\Service\Content\Nodes\Heading;
use App\Service\Content\Nodes\HorizontalRule;
use App\Service\Content\Nodes\Image;
use App\Service\Content\Nodes\Paragraph;
use App\Service\Content\Nodes\Text;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Types\Schema;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ContentService
{

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    const DEFAULT_CONTENT = <<<JSON
{
    "type": "doc"
}
JSON;

    public function htmlFromIssue(Issue $issue): string
    {
        $content = $issue->getContent();

        if (!$content) {
            return '';
        }

        return $this->getHtmlFromJson($content);
    }

    public function textFromIssue(Issue $issue): string
    {
        $content = $issue->getContent();

        if (!$content) {
            return '';
        }

        $document = Document::fromJson($this->getSchema(), $content);
        return $document->toText();
    }

    public function getHtmlFromJson(string $content): string
    {
        $document = Document::fromJson($this->getSchema(), $content);
        return $document->toHtml();
    }

    public function getJsonFromHtml(string $html, bool $sanitize = true): string
    {
        return $this->getDocumentFromHtml($html, $sanitize)->toJson();
    }

    public function getDocumentFromHtml(
        string $html,
        bool $sanitize = true
    ): Node {
        $schema = $this->getSchema();
        $parser = HtmlParser::fromSchema($schema);
        return $parser->parse($html, sanitize: $sanitize);
    }

    public function getSchema(): Schema
    {
        return new Schema(
            [
                new Doc(),
                new Paragraph(),
                new Text(),
                new HardBreak(),
                new Image(),
                new Heading(),
                new HorizontalRule(),
                new Blockquote(),
                new Button()
            ],
            [
                new Em(),
                new Strong(),
                new Link(),
                new Underline(),
                new Strike(),
                new Code(),
            ]
        );
    }

    public function getDefaultContentStyleHtml(): string
    {
        return (string)file_get_contents($this->projectDir . '/templates/newsletter/content-styles.html');
    }

}
