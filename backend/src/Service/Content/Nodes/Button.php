<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Button extends NodeType
{
    public string $name = 'button';
    public ?string $content = 'block';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        // TODO: Add Styling
        return "<button>$children</button>";
    }
}
