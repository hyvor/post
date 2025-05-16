<?php

namespace App\Api\Console\Input\List;

use Symfony\Component\Validator\Constraints as Assert;

class CreateListInput
{

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\Type('string')]
    public ?string $description = null;
}
