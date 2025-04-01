<?php

namespace App\Content\Nodes;

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

}
