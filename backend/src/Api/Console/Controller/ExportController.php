<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Object\SubscriberExportObject;
use App\Entity\Newsletter;
use App\Service\Media\MediaService;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    public function __construct(
        private SubscriberService $subscriberService,
        private MediaService $mediaService,
    ) {}

    #[Route('/export', methods: 'POST')]
    #[ScopeRequired(PostScope::DATA_WRITE)]
    public function exportSubscribers(Newsletter $newsletter): JsonResponse
    {
        $subscriberExport = $this->subscriberService->exportSubscribers($newsletter);
        return $this->json(new SubscriberExportObject($subscriberExport, null));
    }

    #[Route('/export', methods: 'GET')]
    #[ScopeRequired(PostScope::DATA_READ)]
    public function listExports(Newsletter $newsletter): JsonResponse
    {
        $exports = $this->subscriberService->getExports($newsletter);
        $exportObjects = array_map(function ($export) {
            $media = $export->getMedia();
            if ($media) {
                return new SubscriberExportObject($export, $this->mediaService->getPublicUrl($media));
            }
            return new SubscriberExportObject($export, null);
        }, $exports);
        return $this->json($exportObjects);
    }
}
