<?php

namespace App\Service\Integration\Aws;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Support\Facades\Http;

class SesService
{

    public static function validateSnsRequest(array $data): bool
    {
        $message = new Message($data);
        $validator = app(MessageValidator::class, [
            'certClient' => function ($certUrl) {
                return Cache::remember($certUrl, now()->addMonth(), function () use ($certUrl) {
                    return Http::get($certUrl)->body();
                });
            }
        ]); // mockable


        $validator = new MessageValidator();

        return $validator->isValid($message);
    }

}
