<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Object\SubscriberExportObject;
use App\Entity\Newsletter;
use App\Service\Media\MediaService;
use App\Service\Subscriber\SubscriberService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class ExportsController extends AbstractController
{
    public function __construct(
        private SubscriberService $subscriberService,
        private MediaService $mediaService,
    ) {}

    #[Route('/export', methods: 'POST')]
    #[ScopeRequired(PostScope::DATA_WRITE)]
    #[OA\Post(
        description: 'Starts a new export of all subscribers of the newsletter.',
        summary: 'Export subscribers',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created subscriber export object.',
        content: new Model(type: SubscriberExportObject::class),
    )]
    public function create(Newsletter $newsletter): JsonResponse
    {
        $subscriberExport = $this->subscriberService->exportSubscribers($newsletter);
        return $this->json(new SubscriberExportObject($subscriberExport, null));
    }

    #[Route('/export', methods: 'GET')]
    #[ScopeRequired(PostScope::DATA_READ)]
    #[OA\Get(
        description: 'Get all subscriber exports of the newsletter.',
        summary: 'Get subscriber exports',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of subscriber exports',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SubscriberExportObject::class)),
        ),
    )]
    public function list(Newsletter $newsletter): JsonResponse
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
