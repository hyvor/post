<?php

namespace App\Service\Project;

/**
 * TemplateDefaults are separately defined
 */
class ProjectDefaults
{

    public const FORM_COLOR_TEXT = 'inherit';
    // depends on --hp-color-text
    public const FORM_COLOR_TEXT_LIGHT = 'color-mix(in srgb, var(--hp-color-text) 60%, transparent)';
    public const FORM_COLOR_LIGHT_ACCENT = '#000';
    public const FORM_COLOR_LIGHT_ACCENT_TEXT = '#fff';
    public const FORM_COLOR_LIGHT_INPUT = '#fff';
    public const FORM_COLOR_LIGHT_INPUT_TEXT = 'inherit';

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
    public const TEMPLATE_COLOR_ACCENT = '#007bff';
    public const TEMPLATE_COLOR_BACKGROUND = '#f8f9fa';
    public const TEMPLATE_COLOR_BOX_BACKGROUND = '#ffffff';

    public const TEMPLATE_FONT_FAMILY = 'Arial, sans-serif';
    public const TEMPLATE_FONT_SIZE = '16px';
    public const TEMPLATE_FONT_WEIGHT = 'normal';
    public const TEMPLATE_FONT_WEIGHT_HEADING = 'bold';
    public const TEMPLATE_FONT_COLOR_ON_BACKGROUND = '#007bff';
    public const TEMPLATE_FONT_COLOR_ON_BOX = '#333333';
    public const TEMPLATE_FONT_LINE_HEIGHT = '1.5';

    public const TEMPLATE_BOX_RADIUS = '5px';
    public const TEMPLATE_BOX_SHADOW = '0 0 10px rgba(0, 0, 0, 0.1)';
    public const TEMPLATE_BOX_BORDER = '1px solid #e9ecef';

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

}
