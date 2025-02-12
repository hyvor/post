<?php

namespace App\Api\Console\InputObject;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProjectInputObject
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

}