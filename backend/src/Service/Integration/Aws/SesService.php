<?php

namespace App\Service\Integration\Aws;

use Aws\SesV2\SesV2Client;

class SesService
{

    public const string NEWSLETTER_CONFIGURATION_SET_NAME = 'newsletter';

    public function getClient(): SesV2Client
    {
        return new SesV2Client([
            'region' => $_ENV['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

}
