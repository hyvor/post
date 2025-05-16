<?php

namespace App\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class HeadingAttrs extends AttrsType
{
    public int $level = 2;
}
