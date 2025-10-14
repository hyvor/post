<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class HorizontalRule extends NodeType
{
    public string $name = 'horizontal_rule';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return '<hr />';
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'hr'),
        ];
    }

}
