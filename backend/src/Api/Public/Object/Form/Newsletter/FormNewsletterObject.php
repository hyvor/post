<?php

namespace App\Api\Public\Object\Form\Newsletter;

use App\Entity\Newsletter;

class FormNewsletterObject
{

    public string $uuid;
    public FormObject $form;
    public PaletteObject $palette_light;
    public PaletteObject $palette_dark;

    public function __construct(Newsletter $project)
    {
        $this->uuid = $project->getUuid();
        $meta = $project->getMeta();
        $this->form = new FormObject($meta);

        $this->palette_light = new PaletteObject($meta, 'light');
        $this->palette_dark = new PaletteObject($meta, 'dark');
    }

}