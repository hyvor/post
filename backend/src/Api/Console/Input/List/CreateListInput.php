<?php

namespace App\Api\Console\Input\List;

use Symfony\Component\Validator\Constraints as Assert;

class CreateListInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;
}
