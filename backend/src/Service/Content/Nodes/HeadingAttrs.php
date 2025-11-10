<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class HeadingAttrs extends AttrsType
{
    public int $level = 2;

    public ?string $id = null;

}
