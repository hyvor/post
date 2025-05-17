<?php

namespace App\Api\Public\Object\Form\Project;

use App\Entity\Meta\ProjectMeta;
use App\Service\Project\ProjectDefaults;

class PaletteObject
{

    public ?string $text;

    public string $accent;
    public string $accent_text;
    public string $input;
    public string $input_text;

    /**
     * @param 'light' | 'dark' $mode
     */
    public function __construct(ProjectMeta $meta, string $mode)
    {
        $this->text = $meta->{'form_color_' . $mode . '_text'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_TEXT'};
        $this->accent = $meta->{'form_color_' . $mode . '_accent'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_ACCENT'};
        $this->accent_text = $meta->{'form_color_' . $mode . '_accent_text'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_ACCENT_TEXT'};
        $this->input = $meta->{'form_color_' . $mode . '_input'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_INPUT'};
        $this->input_text = $meta->{'form_color_' . $mode . '_input_text'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_INPUT_TEXT'};
    }

}
