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
            // Support for <u> tag
            new ParserRule(
                tag: 'u',
                getAttrs: fn(\DOMElement $dom) => []
            ),

            // Support for inline style "text-decoration: underline"
            new ParserRule(
                tag: 'span',
                getAttrs: function (\DOMElement $dom): ?array {
                    $style = $dom->getAttribute('style') ?? '';
                    $style = strtolower($style);
                    if (str_contains($style, 'text-decoration') && str_contains($style, 'underline')) {
                        return [];
                    }
                    return null;
                }
            )
        ];
    }
}
