<?php

namespace App\Api\Public\Object\Form\Newsletter;

use App\Entity\Newsletter;

class FormNewsletterObject
{

    public string $subdomain;
    public string $direction;
    public FormObject $form;
    public PaletteObject $palette_light;
    public PaletteObject $palette_dark;

    public function __construct(Newsletter $newsletter)
    {
        $this->subdomain = $newsletter->getSubdomain();
        $this->direction = $newsletter->isRtl() ? 'rtl' : 'ltr';
        $meta = $newsletter->getMeta();
        $this->form = new FormObject($meta);

        $this->palette_light = new PaletteObject($meta, 'light');
        $this->palette_dark = new PaletteObject($meta, 'dark');
    }

}
