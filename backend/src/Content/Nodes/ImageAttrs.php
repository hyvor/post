<?php

namespace App\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class ImageAttrs extends AttrsType
{
    public string $src;
    public ?string $alt = null;

}
