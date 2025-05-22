<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Paragraph extends NodeType
{
    public string $name = 'paragraph';
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return "<p>$children</p>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'p')
        ];
    }
}
