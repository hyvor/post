<?php

namespace App\Api\Public\Input\Form;

use Symfony\Component\Validator\Constraints as Assert;

class FormRenderInput
{
    #[Assert\NotBlank]
    public string $id;

    public ?string $instance = null;

}
