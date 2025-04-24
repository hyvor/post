<?php

namespace App\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Blockquote extends NodeType
{
    public string $name = 'blockquote';
    public ?string $content = 'block+';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        $styles = "
            border-left: 4px solid;
            border-color: #000;
            border-color: currentColor;
            border-color: var(--accent);
            margin: 0 0 20px;
            padding: 15px;
        ";
        return "<blockquote style=\"$styles\">$children</blockquote>";
    }

}
