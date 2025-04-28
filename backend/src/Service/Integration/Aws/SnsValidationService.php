<?php

namespace App\Service\Integration\Aws;

use Aws\SesV2\SesV2Client;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

class SnsValidationService
{

    public function get_client(): SesV2Client
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

    /**
     * @param array<mixed> $data
     */
    public function validate(array $data): bool
    {
        $message = new Message($data);
        // TODO: add caching here
        $validator = new MessageValidator();
        return $validator->isValid($message);
    }

}
