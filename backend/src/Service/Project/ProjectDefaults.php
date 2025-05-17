<?php

namespace App\Service\Project;

/**
 * TemplateDefaults are separately defined
 */
class ProjectDefaults
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

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

}
