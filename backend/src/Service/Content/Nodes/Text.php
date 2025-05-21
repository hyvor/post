<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Text extends NodeType
{
    public string $name = 'text';
    public string $group = 'inline';
    public bool $inline = true;

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: '#text')
        ];
    }
}
