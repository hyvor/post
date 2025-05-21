<?php

namespace App\Service\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;
use DOMElement;

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

    public function fromHtml(): array
    {

        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node) : LinkAttrs | bool {
                    $href = $node->getAttribute('href');

                    if (!$href)
                        return false;

                    return LinkAttrs::fromArray(['href' => $href]);
                }
            ),
        ];

    }

}
