<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Image extends NodeType
{
    public string $name = 'image';
    public string $group = 'block';
    public string $attrs = ImageAttrs::class;

    public function toHtml(Node $node, string $children): string
    {

        $src = $node->attr('src');
        $alt = $node->attr('alt');

        $attrs = <<<ATTR
            src="$src"
            alt="$alt"
            style="
                display: block;
                margin: 30px auto;
                max-width: 100%;
                height: auto;
            "
        ATTR;

        return "<img $attrs />";
    }

}
