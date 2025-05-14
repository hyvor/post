<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Underline extends MarkType
{
    public string $name = 'underline';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<span style=\"text-decoration:underline\">$children</span>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'underline'),
            new ParserRule(tag: 'u'),
        ];
    }
}
