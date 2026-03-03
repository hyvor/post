<?php

namespace App\Api\Sudo\Controller;

use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterService;
use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
        private SudoObjectFactory $sudoObjectFactory,
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
                fn($newsletter) => $this->sudoObjectFactory->create($newsletter),
                $this->newsletterService->getNewsletters($name, $limit, $offset)
            )
        );
    }

    #[Route('/newsletters/{id}', methods: ['GET'])]
    public function getNewsletter(Newsletter $newsletter): JsonResponse
    {
        return new JsonResponse([
            'newsletter' => $this->sudoObjectFactory->create($newsletter),
            'stats' => $this->newsletterService->getNewsletterStats($newsletter),
        ]);
    }
}
