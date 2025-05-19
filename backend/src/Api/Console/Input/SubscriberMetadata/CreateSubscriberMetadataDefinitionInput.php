<?php

namespace App\Api\Console\Input\SubscriberMetadata;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSubscriberMetadataDefinitionInput
{

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Key can only contain lowercase letters, numbers, and underscores',
    )]
    #[Assert\Length(max: 255)]
    public string $key;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

}