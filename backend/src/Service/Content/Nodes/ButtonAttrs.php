<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class ButtonAttrs extends AttrsType
{

    public ?string $href = null;
    public string $text = '';

}