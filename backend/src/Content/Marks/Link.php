<?php

namespace App\Content\Marks;

use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Link extends MarkType
{
    public string $name = 'link';
    public string $attrs = LinkAttrs::class;

    public function toHtml(Mark $mark, string $children): string
    {

        /** @var string $href */
        $href = $mark->attr('href');

        return "<a href=\"$href\" target=\"_blank\" style=\"color:inherit;text-decoration:underline\">$children</a>";

    }

}
