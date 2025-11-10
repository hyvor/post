<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Button extends NodeType
{
    public string $name = 'button';
    public ?string $content = 'text';
    public string $group = 'block';
    public string $attrs = ButtonAttrs::class;

    public function toHtml(Node $node, string $children): string
    {
        /** @var string $href */
        $href = $node->attr('href');
        /** @var string $text */
        $text = $node->attr('text');

        if (empty($text)) {
            $text = $children;
        }
        return "<p class=\"button-wrap\"><a href=\"$href\" target=\"_blank\" class=\"button\">$text</a></p>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (\DOMElement $node): ButtonAttrs|bool {
                    $class = $node->getAttribute('class');

                    if (!$class || !str_contains($class, 'button')) {
                        return false;
                    }

                    $href = $node->getAttribute('href');
                    $text = $node->textContent;

                    if (!$href || !$text) {
                        return false;
                    }

                    return ButtonAttrs::fromArray(['href' => $href]);
                }
            ),
        ];
    }
}
