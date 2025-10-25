<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Object\SubscriberImportObject;
use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\ImportService;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberImportController extends AbstractController
{
    public function __construct(
        private ImportService     $importService,
        private NewsletterService $newsletterService
    )
    {
    }

    #[Route('/subscriber-imports', methods: ['GET'])]
    public function getSubscriberImportsForApproval(
        Request $request
    ): JsonResponse
    {
        $subdomain = $request->query->has('subdomain') ? $request->query->getString('subdomain') : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $newsletter = $subdomain ? $this->newsletterService->getNewsletterBySubdomain($subdomain) : null;

        return new JsonResponse(
            array_map(
                fn($import) => new SubscriberImportObject($import),
                $this->importService->getSubscriberImports(
                    $newsletter,
                    SubscriberImportStatus::PENDING_APPROVAL,
                    limit: $limit,
                    offset: $offset
                )
            )
        );
    }

    #[Route('/subscriber-imports/{id}', methods: ['GET'])]
    public function getImportingSubscribers(SubscriberImport $subscriberImport): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/subscriber-imports/{id}', methods: ['POST'])]
    public function approveSubscriberImport(SubscriberImport $subscriberImport): JsonResponse
    {
        return new JsonResponse();
    }
}