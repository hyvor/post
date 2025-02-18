<?php

namespace App\Tests\Api\Console;

use App\Tests\Case\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

// TODO: remove this
class TestTest extends WebTestCase
{

    public function testTest(): void
    {

        $this->client->getCookieJar()->set(new Cookie('authsess', 'test'));
        $this->client->request('GET', '/api/console/test');
        $content = $this->client->getResponse()->getContent();

        dd($content);
    }

}