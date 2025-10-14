<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Sup extends MarkType
{

    public string $name = 'sup';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<sup>$children</sup>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'sup')
        ];
    }

}