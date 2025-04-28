<?php

namespace App\Service\Integration\Aws;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

class SnsValidationService
{

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
