<?php

namespace App\Api\Console\Input\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNewsletterInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

}