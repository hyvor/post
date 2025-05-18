<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Strong extends MarkType
{
    public string $name = 'strong';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<strong>$children</strong>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'strong'),
            new ParserRule(tag: 'b'),
        ];
    }

}
