<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class ListItem extends NodeType
{

    public string $name = 'list_item';
    public ?string $content = 'block*';

    public function toHtml(Node $node, string $children): string
    {
        return "<li>$children</li>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'li')
        ];
    }

}