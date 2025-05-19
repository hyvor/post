<?php

namespace App\Api\Console\Input\SubscriberMetadata;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateSubscriberMetadataDefinitionInput
{

    #[Assert\NotBlank]
    public string $name;

}