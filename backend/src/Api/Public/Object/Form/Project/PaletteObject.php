<?php

namespace App\Api\Public\Object\Form\Project;

use App\Entity\Meta\ProjectMeta;
use App\Service\Project\ProjectDefaults;

class PaletteObject
{

    public string $accent;
    public string $accent_text;

    /**
     * @param 'light' | 'dark' $mode
     */
    public function __construct(ProjectMeta $meta, string $mode)
    {
        $this->accent = $meta->{'form_color_' . $mode . '_accent'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_ACCENT'};
        $this->accent_text = $meta->{'form_color_' . $mode . '_accent_text'} ?? ProjectDefaults::{'FORM_COLOR_' . strtoupper(
                $mode
            ) . '_ACCENT_TEXT'};
    }

}