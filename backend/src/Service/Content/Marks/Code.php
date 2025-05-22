<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Code extends MarkType
{
    public string $name = 'code';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<code>$children</code>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'code')
        ];
    }

}
