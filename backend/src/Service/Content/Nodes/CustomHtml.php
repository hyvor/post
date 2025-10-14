<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\NodeType;

class CustomHtml extends NodeType
{

    public string $name = 'custom_html';
    public ?string $content = 'text*';
    public string $group = 'block';

    public function toHtml($node, $children): string
    {
        $code = $node->allText();
        return "<div>$code</div>";
    }

}