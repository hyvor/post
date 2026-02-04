<?php

namespace App\Api\Root;

use App\Service\Newsletter\NewsletterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class RootApiController
{
    public function __construct(private NewsletterService $newsletterService)
    {
    }

    #[Route('/api/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        // to force database connection
        $this->newsletterService->getNewsletterById(1);

        return new JsonResponse(['status' => 'ok']);
    }
}
