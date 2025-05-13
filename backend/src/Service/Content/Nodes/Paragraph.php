<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Paragraph extends NodeType
{
    public string $name = 'paragraph';
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        $style = "margin: 0 0 20px;line-height:26px;";
        return "<p style=\"$style\">$children</p>";
    }

}
