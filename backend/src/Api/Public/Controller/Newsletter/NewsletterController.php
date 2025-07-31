<?php

namespace App\Api\Public\Controller\Newsletter;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{

    #[Route('/newsletter', methods: 'GET')]
    public function getNewsletter(): JsonResponse
    {
        return new JsonResponse();
    }

}
