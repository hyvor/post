<?php

namespace App\Api\Console\Input\Project;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProjectInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

}