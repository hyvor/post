<?php

namespace App\Content\Marks;

use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Strike extends MarkType
{
    public string $name = 'strike';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<s>$children</s>";
    }

}
