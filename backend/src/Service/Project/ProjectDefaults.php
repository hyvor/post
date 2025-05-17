<?php

namespace App\Service\Project;

/**
 * TemplateDefaults are separately defined
 */
class ProjectDefaults
{

    public const FORM_COLOR_LIGHT_TEXT = '#000';
    public const FORM_COLOR_LIGHT_ACCENT = '#000';
    public const FORM_COLOR_LIGHT_ACCENT_TEXT = '#fff';
    public const FORM_COLOR_LIGHT_INPUT = '#fff';
    public const FORM_COLOR_LIGHT_INPUT_TEXT = '#000';

    public const FORM_COLOR_DARK_TEXT = '#fff';
    public const FORM_COLOR_DARK_ACCENT = '#fff';
    public const FORM_COLOR_DARK_ACCENT_TEXT = '#000';
    public const FORM_COLOR_DARK_INPUT = '#000';
    public const FORM_COLOR_DARK_INPUT_TEXT = '#fff';

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

}
