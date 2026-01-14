<?php

namespace App\Service\Content;

use App\Entity\Issue;
use App\Service\Content\Marks\Code;
use App\Service\Content\Marks\Em;
use App\Service\Content\Marks\Link;
use App\Service\Content\Marks\Strike;
use App\Service\Content\Marks\Strong;
use App\Service\Content\Marks\Sub;
use App\Service\Content\Marks\Sup;
use App\Service\Content\Marks\Underline;
use App\Service\Content\Nodes\Blockquote;
use App\Service\Content\Nodes\BulletList;
use App\Service\Content\Nodes\Button;
use App\Service\Content\Nodes\Callout;
use App\Service\Content\Nodes\CodeBlock;
use App\Service\Content\Nodes\CustomHtml;
use App\Service\Content\Nodes\Doc;
use App\Service\Content\Nodes\Figcaption;
use App\Service\Content\Nodes\Figure;
use App\Service\Content\Nodes\HardBreak;
use App\Service\Content\Nodes\Heading;
use App\Service\Content\Nodes\HorizontalRule;
use App\Service\Content\Nodes\Image;
use App\Service\Content\Nodes\ListItem;
use App\Service\Content\Nodes\OrderedList;
use App\Service\Content\Nodes\Paragraph;
use App\Service\Content\Nodes\Text;
use App\Service\Template\TemplateVariables;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Types\Schema;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ContentService
{

    public function __construct(
        private CustomHtmlTwigProcessorFactory $customHtmlTwigProcessorFactory
    )
    {
    }

    const string DEFAULT_CONTENT = <<<JSON
{
    "type": "doc"
}
JSON;

    public function getHtmlFromJson(string $content, ?TemplateVariables $variables = null): string
    {
        $document = Document::fromJson($this->getSchema($variables), $content);
        return $document->toHtml();
    }

    public function getTextFromJson(string $content): string
    {
        $document = Document::fromJson($this->getSchema(), $content);
        return $document->toText();
    }

    public function getJsonFromHtml(string $html, bool $sanitize = true): string
    {
        return $this->getDocumentFromHtml($html, $sanitize)->toJson();
    }

    public function getDocumentFromHtml(
        string $html,
        bool   $sanitize = true
    ): Node
    {
        $schema = $this->getSchema();
        $parser = HtmlParser::fromSchema($schema);
        return $parser->parse($html, sanitize: $sanitize);
    }

    public function getSchema(?TemplateVariables $variables = null): Schema
    {
        $twigProcessor = $this->customHtmlTwigProcessorFactory->create($variables);

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
                new Button(),
                new BulletList(),
                new OrderedList(),
                new ListItem(),
                new Figure(),
                new Figcaption(),
                new CodeBlock(),
                new CustomHtml($twigProcessor),
                new Callout(),
            ],
            [
                new Em(),
                new Strong(),
                new Link(),
                new Strike(),
                new Code(),
                new Sub(),
                new Sup(),
            ]
        );
    }

}
