<?php

namespace App\Api\Public\Input;

use Symfony\Component\Validator\Constraints as Assert;

class AwsWebhookInput
{

    #[Assert\NotBlank]
    public string $Type;

    public string $SubscribeURL;

    public string $Message;
}
