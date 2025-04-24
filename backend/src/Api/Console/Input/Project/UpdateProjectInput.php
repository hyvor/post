<?php

namespace App\Api\Console\Input\Project;

use App\Util\OptionalPropertyTrait;

class UpdateProjectInput
{
    use OptionalPropertyTrait;

    public ?string $template_color_accent;
    public ?string $template_color_background;
    public ?string $template_color_box_background;
    public ?string $template_color_box_shadow;
    public ?string $template_color_box_border;
    public ?string $template_font_family;
    public ?string $template_font_size;
    public ?string $template_font_weight;
    public ?string $template_font_weight_heading;
    public ?string $template_font_color_on_background;
    public ?string $template_font_color_on_box;
    public ?string $template_font_line_height;
    public ?string $template_box_radius;
    public ?string $template_logo;

    /**
     * @return array<string>
     */
    public function getSetProperties(): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($this->hasProperty($property)) {
                $properties[] = $property;
            }
        }
        return $properties;
    }
}
