<?php

namespace App\Tests\Case;

use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    public function consoleApi(string $method, string $uri, array $data = []): Response
    {
        $client = static::createClient();
        // TODO: add authentication here
        $client->request(
            $method,
            '/api/console' . $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($data)
        );
        return $client->getResponse();
    }

}