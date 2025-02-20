<?php

namespace App\Api\Console\Input\List;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateListInput
{
    #[Assert\Type('string')]
    public ?string $name = null;
}
