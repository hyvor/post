<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Object\NewsletterObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
    )
    {
    }

    #[Route('/newsletters', methods: ['GET'])]
    public function getNewsletters(Request $request): JsonResponse
    {
        $name = $request->query->has('name') ? $request->query->getString('name') : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        return new JsonResponse(
            array_map(
                fn($newsletter) => new NewsletterObject($newsletter),
                $this->newsletterService->getNewsletters($name, $limit, $offset)
            )
        );
    }

    #[Route('/newsletters/{id}', methods: ['GET'])]
    public function getNewsletter(Newsletter $newsletter): JsonResponse
    {
        return new JsonResponse([
            'newsletter' => new NewsletterObject($newsletter),
            'stats' => $this->newsletterService->getNewsletterStats($newsletter),
        ]);
    }
}
