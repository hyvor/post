<?php

namespace App\Api\Test;

use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TestController
{

    #[Route('/', methods: 'PATCH')]
    public function testRoute(
        #[MapRequestPayload] Dto $dto
    ): void
    {

        dd(property_exists($dto, 'email'));

    }

}

class Dto {
    public ?string $email;
}