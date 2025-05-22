<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Blockquote extends NodeType
{
    public string $name = 'blockquote';
    public ?string $content = 'block+';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return "<blockquote>$children</blockquote>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'blockquote')
        ];
    }
}
