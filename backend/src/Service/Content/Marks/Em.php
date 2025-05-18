<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Em extends MarkType
{
    public string $name = 'em';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<em>$children</em>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'em'),
            new ParserRule(tag: 'i'),
        ];
    }
}
