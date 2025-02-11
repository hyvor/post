<?php

namespace App\Api\Resource\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController
{

    #[Route('/test', methods: ['GET'])]
    public function test(): Response
    {
        return new Response('Test');
    }

}