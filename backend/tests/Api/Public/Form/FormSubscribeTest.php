<?php

namespace App\Tests\Api\Public\Form;

use App\Tests\Case\WebTestCase;

class FormSubscribeTest extends WebTestCase
{

    public function test_rate_limiter_is_applied(): void
    {
        $response = $this->publicApi('POST', '/form/subscribe');
        $this->assertResponseStatusCodeSame(429, $response);
    }

}
