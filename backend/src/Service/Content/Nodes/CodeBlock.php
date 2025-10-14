<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlParser\Whitespace;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class CodeBlock extends NodeType
{

    public string $name = 'code_block';
    public ?string $content = 'text*';
    public string $group = 'block';

    public function __construct() {}

    public function toHtml(Node $node, string $children): string
    {
        return "<pre><code>$children</code></pre>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'pre',
                getChildren: function ($node) {
                    /** @var \DOMDocument $document */
                    $document = $node->ownerDocument;

                    $text = $node->textContent ?? '';
                    $text = trim($text);
                    $text = $text ?: ' '; // prevents codemirror error when empty

                    return $document->createTextNode($text);
                },
                whitespace: Whitespace::PRESERVE
            )
        ];
    }

}