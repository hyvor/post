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
        $styles = "background: rgba(135, 131, 120, 0.15);color: #eb5757;border-radius: 3px;font-size: 0.85em;padding: 0.2em 0.4em;font-family: monospace;";
        return "<code style=\"$styles\">$children</code>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'code')
        ];
    }

}
