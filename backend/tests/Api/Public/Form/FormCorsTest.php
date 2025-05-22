<?php

namespace App\Tests\Api\Public\Form;

use App\Tests\Case\WebTestCase;
use Symfony\Component\Uid\Uuid;

class FormCorsTest extends WebTestCase
{

    public function test_adds_cors(): void
    {
        $origin = 'https://example.com';

        $response = $this->publicApi(
            'OPTIONS',
            '/form/init',
            [
                'newsletter_uuid' => Uuid::v4(),
            ],
            headers: [
                'Origin' => $origin,
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'Content-Type',
            ]
        );

        $this->assertResponseStatusCodeSame(200, $response);
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', $origin, $response);
        $this->assertResponseHeaderSame('Access-Control-Allow-Methods', 'POST, OPTIONS', $response);
    }

}