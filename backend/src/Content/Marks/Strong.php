<?php

namespace App\Content\Marks;

use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Strong extends MarkType
{
    public string $name = 'strong';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<strong>$children</strong>";
    }

}
