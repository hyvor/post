<?php

namespace App\Api\Public\Object\Form\Newsletter;

use App\Entity\Meta\NewsletterMeta;

class FormObject
{

    public ?int $width;
    public ?string $custom_css;

    public ?string $title;
    public ?string $description;
    public ?string $footer_text;
    public ?string $button_text;
    public ?string $success_message;

    public function __construct(NewsletterMeta $meta)
    {

        $this->width = $meta->form_width;
        $this->custom_css = $meta->form_custom_css;
        $this->title = $meta->form_title;
        $this->description = $meta->form_description;
        $this->footer_text = $meta->form_footer_text;
        $this->button_text = $meta->form_button_text;
        $this->success_message = $meta->form_success_message;


    }

}