<?php

namespace App\Service\Newsletter;

/**
 * TemplateDefaults are separately defined
 */
class NewsletterDefaults
{

    public const FORM_COLOR_LIGHT_TEXT = '#000000';
    public const FORM_COLOR_LIGHT_ACCENT = '#000000';
    public const FORM_COLOR_LIGHT_ACCENT_TEXT = '#ffffff';
    public const FORM_COLOR_LIGHT_INPUT = '#ffffff';
    public const FORM_COLOR_LIGHT_INPUT_TEXT = '#000000';
    public const FORM_LIGHT_INPUT_BOX_SHADOW = '0px 0px 8px 0px #0000001c';
    public const FORM_LIGHT_INPUT_BORDER = '0px solid #000000';
    public const FORM_LIGHT_BORDER_RADIUS = 20;

    public const FORM_COLOR_DARK_TEXT = '#fff';
    public const FORM_COLOR_DARK_ACCENT = '#fff';
    public const FORM_COLOR_DARK_ACCENT_TEXT = '#000';
    public const FORM_COLOR_DARK_INPUT = '#232323';
    public const FORM_COLOR_DARK_INPUT_TEXT = '#fff';
    public const FORM_DARK_INPUT_BOX_SHADOW = '0px 0px 8px 0px #ffffff87';
    public const FORM_DARK_INPUT_BORDER = '0px solid #fff';
    public const FORM_DARK_BORDER_RADIUS = 20;

    public const string TEMPLATE_LANG = 'en';
//    public const string TEMPLATE_subject = 'Introducing Hyvor Post';
//    public const string TEMPLATE_content =
//        <<<HTML
//            <h1>
//                Introducing Hyvor Post
//            </h1>
//            <p>
//                We are excited to introduce Hyvor Post, a simple newsletter platform. With Hyvor Post, you can collect emails, create newsletters, and send them to your subscribers.
//            </p>
//        HTML;
    public const TEMPLATE_COLOR_ACCENT = '#5A8387';
    public const TEMPLATE_COLOR_ACCENT_TEXT = '#ffffff';
    public const TEMPLATE_COLOR_BACKGROUND = '#f8f9fa';
    public const TEMPLATE_COLOR_BACKGROUND_TEXT = '#4a4a4a';
    public const TEMPLATE_COLOR_BOX = '#ffffff';
    public const TEMPLATE_COLOR_BOX_TEXT = '#000';

    public const TEMPLATE_BOX_RADIUS = '20px';
    public const TEMPLATE_BOX_SHADOW = '0px 0px 8px 0px #0000001a';
    public const TEMPLATE_BOX_BORDER = '0px solid #e9ecef';

    public const TEMPLATE_FONT_FAMILY = "'SF Pro Display', -apple-system-headline, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'";
    public const TEMPLATE_FONT_SIZE = '16px';
    public const TEMPLATE_FONT_WEIGHT = 'normal';
    public const TEMPLATE_FONT_WEIGHT_HEADING = 'bold';
    public const TEMPLATE_FONT_LINE_HEIGHT = '1.6';

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

}
