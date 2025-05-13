<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\NodeType;

class Doc extends NodeType
{
    public string $name = 'doc';
    public ?string $content = 'block+';
}
