<?php

namespace App\Api\Console\Input\Issue;

use Symfony\Component\Validator\Constraints as Assert;

class SendTestInput
{
    /**
     * @var string[]
     */
    #[Assert\Count(min: 1, minMessage: "There should be at least one email.")]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Email,
        new Assert\Length(max: 255),
    ])]
    public array $emails;
}
