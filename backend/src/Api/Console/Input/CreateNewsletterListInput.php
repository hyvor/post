<?php

namespace App\Api\Console\Input;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNewsletterListInput
{
    #[Assert\NotBlank]
    public int $project_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;
}
