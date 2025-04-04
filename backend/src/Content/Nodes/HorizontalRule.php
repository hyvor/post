<?php

namespace App\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class HorizontalRule extends NodeType
{
    public string $name = 'horizontal_rule';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return '<hr style="margin:0 0 20px;height:1px;background-color:currentColor;opacity:0.3" />';
    }

}
