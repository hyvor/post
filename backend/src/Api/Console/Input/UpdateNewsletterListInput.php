<?php

namespace App\Api\Console\Input;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateNewsletterListInput
{
    #[Assert\Type('string')]
    public ?string $name = null;

    #[Assert\Type('integer')]
    public ?int $project_id = null;

    public function __construct(?string $name = null, ?int $project_id = null)
    {
        $this->name = $name;
        $this->project_id = $project_id;
    }
}
