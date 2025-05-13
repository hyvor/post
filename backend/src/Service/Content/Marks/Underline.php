<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Underline extends MarkType
{
    public string $name = 'underline';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<span style=\"text-decoration:underline\">$children</span>";
    }

}
