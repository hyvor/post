<?php

namespace App\Api\Console\Input\Domain;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDomainInput
{
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/',
        message: 'The domain must be a valid domain name.'
    )]
    public string $domain;
}
