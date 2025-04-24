<?php

namespace App\Api\Public\Input;

use Symfony\Component\Validator\Constraints as Assert;

class RetrieveContentHtmlInput
{
    #[Assert\NotBlank]
    public string $content;
}
