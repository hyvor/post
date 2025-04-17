<?php

namespace App\Api\Console\Input\Project;

use App\Util\OptionalPropertyTrait;

class UpdateProjectMetaInput
{
    use OptionalPropertyTrait;

    public ?string $template_color_accent = null;
    public ?string $template_color_background = null;
    public ?string $template_color_box_background = null;
    public ?string $template_color_box_shadow = null;
    public ?string $template_color_box_border = null;
    public ?string $template_font_family = null;
    public ?string $template_font_size = null;
    public ?string $template_font_weight = null;
    public ?string $template_font_weight_heading = null;
    public ?string $template_font_color_on_background = null;
    public ?string $template_font_color_on_box = null;
    public ?string $template_font_line_height = null;
    public ?string $template_box_radius = null;
    public ?string $template_logo = null;

    /**
     * @return array<string>
     */
    public function getSetProperties(): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== null) {
                $properties[] = $property;
            }
        }
        return $properties;
    }
}
