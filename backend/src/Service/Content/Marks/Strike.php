<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Strike extends MarkType
{
    public string $name = 'strike';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<s>$children</s>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 's'),
            new ParserRule(tag: 'del'),
            new ParserRule(tag: 'strike')
        ];
    }
}
