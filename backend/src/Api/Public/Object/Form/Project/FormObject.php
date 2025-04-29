<?php

namespace App\Api\Public\Object\Form\Project;

use App\Entity\Meta\ProjectMeta;

class FormObject
{


    public ?string $title;
    public ?string $description;
    public ?string $footer_text;
    public ?string $button_text;
    public ?string $success_message;

    public function __construct(ProjectMeta $meta)
    {

        $this->title = $meta->form_title;
        $this->description = $meta->form_description;
        $this->footer_text = $meta->form_footer_text;
        $this->button_text = $meta->form_button_text;
        $this->success_message = $meta->form_success_message;


    }

}