<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\NodeType;

class Text extends NodeType
{
    public string $name = 'text';
    public string $group = 'inline';
    public bool $inline = true;

}
