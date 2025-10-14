<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use Hyvor\Phrosemirror\Util\InlineStyle;
use Symfony\Component\DomCrawler\Crawler;

class Callout extends NodeType
{

    public string $name = 'callout';
    public string $attrs = CalloutAttrs::class;
    public ?string $content = 'inline*';
    public string $group = 'block';

    public const string DEFAULT_EMOJI = '💡';
    public const string DEFAULT_BG = '#f1f1ef';
    public const string DEFAULT_FG = '#000000';

    public function __construct() {}

    public function toHtml(Node $node, string $children): string
    {

        /** @var string $bg */
        $bg = $node->attr('bg');
        /** @var string $fg */
        $fg = $node->attr('fg');
        /** @var string $emoji */
        $emoji = $node->attr('emoji');

        return "<div class=\"callout\" style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$children</div></div>";

    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'div',
                getAttrs: function (\DOMElement $node) {
                    $class = $node->getAttribute('class');
                    if (!$class || !str_contains($class, 'callout')) {
                        return false;
                    }

                    $bg = InlineStyle::getAttribute($node, 'background-color');
                    $fg = InlineStyle::getAttribute($node, 'color');

                    $crawler = new Crawler($node);
                    $span = $crawler->filter('span');
                    $emoji = $span->count() ? $span->first()->text() : null;

                    return CalloutAttrs::fromArray([
                        'bg' => $bg ?? self::DEFAULT_BG,
                        'fg' => $fg ?? self::DEFAULT_FG,
                        'emoji' => $emoji ?? self::DEFAULT_EMOJI,
                    ]);

                },

                getChildren: function (\DOMElement $node) {
                    $crawler = new Crawler($node);
                    $returnNode = false;

                    $crawler->filter('div')->each(function (Crawler $node) use (&$returnNode) {
                        $innerNode = $node->getNode(0);
                        if ($innerNode) {
                            $returnNode = $innerNode;
                        }
                    });

                    return $returnNode;
                }
            )
        ];
    }


}