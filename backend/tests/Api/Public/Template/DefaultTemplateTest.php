<?php

namespace App\Tests\Api\Public\Template;

use App\Tests\Case\WebTestCase;

class DefaultTemplateTest extends WebTestCase
{

    public function test_default_template(): void
    {
        $response = $this->publicApi('GET', '/template/default');
        $this->assertSame(200, $response->getStatusCode());
    }
}
