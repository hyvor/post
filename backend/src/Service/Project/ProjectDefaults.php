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
    public const FORM_COLOR_LIGHT_INPUT_TEXT = '#000';

    public const FORM_COLOR_DARK_ACCENT = '#fff';
    public const FORM_COLOR_DARK_ACCENT_TEXT = '#000';

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

}