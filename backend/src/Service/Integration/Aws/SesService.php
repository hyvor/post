<?php

namespace App\Service\Integration\Aws;

use App\Service\AppConfig;
use Aws\SesV2\SesV2Client;

class SesService
{

    public function __construct(
        private readonly AppConfig $appConfig
    )
    {
    }

    public function getClient(): SesV2Client
    {
        return new SesV2Client([
            'region' => $this->appConfig->getAwsSesRegion(),
            'version' => 'latest',
            'credentials' => [
                'key' => $this->appConfig->getAwsSesAccessKeyId(),
                'secret' => $this->appConfig->getAwsSesSecretAccessKey(),
            ],
        ]);
    }

}
