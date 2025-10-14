<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Sub extends MarkType
{

    public string $name = 'sub';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<sub>$children</sub>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'sub')
        ];
    }

}