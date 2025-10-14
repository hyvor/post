<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class CalloutAttrs extends AttrsType
{
    public ?string $emoji;
    public ?string $bg;
    public ?string $fg;
}