<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class HardBreak extends NodeType
{
    public string $name = 'hard_break';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return '<br />';
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'br')
        ];
    }

}
