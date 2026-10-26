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
                'newsletter_subdomain' => 'test',
            ],
            headers: [
                'Origin' => $origin,
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'Content-Type',
            ]
        );

        $this->assertResponseStatusCodeSame(200, $response);
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', $origin, $response);
        $this->assertResponseHeaderSame('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PATCH', $response);
    }

    public function test_exposes_retry_after_header(): void
    {
        $response = $this->publicApi(
            'POST',
            '/form/init',
            [
                'newsletter_subdomain' => 'test',
            ],
            headers: [
                'Origin' => 'https://example.com',
            ]
        );

        $this->assertResponseHeaderSame('Access-Control-Expose-Headers', 'link, retry-after', $response);
    }

}
