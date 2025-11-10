<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class ImageAttrs extends AttrsType
{
    public string $src;
    public ?string $alt = null;
    public null|int|string $width = null;
    public null|int|string $height = null;

}
