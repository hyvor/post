<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use DOMElement;

class Heading extends NodeType
{
    private const ALLOWED_LEVELS = [1, 2, 3, 4, 5, 6];

    public string $name = 'heading';
    public string $attrs = HeadingAttrs::class;
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        $levelAttr = $node->attr('level');
        $level = is_scalar($levelAttr) ? intval($levelAttr) : 2;
        $level = in_array($level, self::ALLOWED_LEVELS) ? $level : 2;

        $fontSizes = [
            1 => '32px',
            2 => '28px',
            3 => '24px',
            4 => '20px',
            5 => '16px',
            6 => '14px',
        ];
        $fontSize = $fontSizes[$level];
        $bottomMargin = '20px';
        $style = "margin: 0 0 $bottomMargin; font-size: $fontSize; font-weight: bold;";

        return "<h$level>$children</h$level>";
    }

    public function fromHtml(): array
    {
        return array_map(function (int $level) {
            return new ParserRule(
                tag: "h{$level}",
                getAttrs: function (DOMElement $node) use ($level) {
                    return HeadingAttrs::fromArray([
                        'level' => $level,
                        'id' => $node->getAttribute('id'),
                    ]);
                },
            );
        }, self::ALLOWED_LEVELS);
    }

}
