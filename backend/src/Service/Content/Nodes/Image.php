<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use DOMElement;

class Image extends NodeType
{
    public string $name = 'image';
    public string $group = 'block';
    public string $attrs = ImageAttrs::class;

    public function toHtml(Node $node, string $children): string
    {
        $src = $node->attr('src');
        $alt = $node->attr('alt');

        assert(is_string($src));
        assert(is_string($alt));

        $width = $node->attr('width');
        $widthAttr = is_string($width) && $width ? " width=\"$width\"" : '';

        $height = $node->attr('height');
        $heightAttr = is_string($height) && $height ? " height=\"$height\"" : '';

        return "<img src=\"$src\" alt=\"$alt\"$widthAttr$heightAttr />";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'img',
                getAttrs: function (DOMElement $node) {
                    $src = $node->getAttribute('src');
                    if (!$src) return false;

                    $data = [
                        'src' => $src
                    ];

                    $alt = $node->getAttribute('alt');
                    $width = $node->getAttribute('width');
                    $height = $node->getAttribute('height');

                    if ($alt) $data['alt'] = $alt;
                    if ($width) $data['width'] = $width;
                    if ($height) $data['height'] = $height;

                    return ImageAttrs::fromArray($data);
                },
            )
        ];
    }

}
