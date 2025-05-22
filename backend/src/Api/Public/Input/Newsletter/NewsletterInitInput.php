<?php

namespace App\Api\Public\Input\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class NewsletterInitInput
{

    #[Assert\NotBlank]
    public string $slug;

}