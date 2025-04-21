<?php

namespace App\Api\Public\Input;

class AwsWebhookInput
{
    public string $Type;

    public string $SubscribeURL;

    public string $Message;
}
