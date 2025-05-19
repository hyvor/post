<?php

namespace App\Api\Console\Input\SubscriberMetadata;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSubscriberMetadataDefinitionInput
{

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_-]+$/',
        message: 'Key can only contain letters, numbers, underscores and dashes',
    )]
    public string $key;

    #[Assert\NotBlank]
    public string $name;

}