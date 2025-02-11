<?php declare(strict_types=1);

namespace App\Api\Console\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{

    #[Route('/test', methods: ['GET'])]
    public function test(): Response
    {
        $user = $this->getUser();

        return new JsonResponse($user);
    }

}