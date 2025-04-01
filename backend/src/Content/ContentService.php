<?php

namespace App\Domain\Newsletter\Content;

use App\Domain\Newsletter\Content\Marks\Code;
use App\Domain\Newsletter\Content\Marks\Em;
use App\Domain\Newsletter\Content\Marks\Link;
use App\Domain\Newsletter\Content\Marks\Strike;
use App\Domain\Newsletter\Content\Marks\Strong;
use App\Domain\Newsletter\Content\Marks\Underline;
use App\Domain\Newsletter\Content\Nodes\Blockquote;
use App\Domain\Newsletter\Content\Nodes\Doc;
use App\Domain\Newsletter\Content\Nodes\HardBreak;
use App\Domain\Newsletter\Content\Nodes\Heading;
use App\Domain\Newsletter\Content\Nodes\HorizontalRule;
use App\Domain\Newsletter\Content\Nodes\Image;
use App\Domain\Newsletter\Content\Nodes\Paragraph;
use App\Domain\Newsletter\Content\Nodes\Text;
use App\Models\NewsletterIssue;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Types\Schema;

class ContentService
{
    public static function htmlFromIssue(NewsletterIssue $issue): string
    {
        $content = $issue->content;

        if (!$content) {
            return '';
        }

        return self::htmlFromJson($content);
    }

    public static function textFromIssue(NewsletterIssue $issue): string
    {
        $content = $issue->content;

        if (!$content) {
            return '';
        }

        $document = Document::fromJson(self::getSchema(), $content);
        return $document->toText();
    }

    public static function htmlFromJson(string $content): string
    {
        $document = Document::fromJson(self::getSchema(), $content);
        return $document->toHtml();
    }

    public static function getSchema(): Schema
    {

        return new Schema(
            [
                new Doc(),
                new Paragraph(),
                new Text(),
                new HardBreak(),
                new Image(),
                new Heading(),
                new HorizontalRule(),
                new Blockquote(),
            ],
            [
                new Em(),
                new Strong(),
                new Link(),
                new Underline(),
                new Strike(),
                new Code(),
            ]
        );

    }

}
