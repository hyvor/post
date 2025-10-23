<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Import\ImportInput;
use App\Api\Console\Object\SubscriberImportObject;
use App\Entity\Newsletter;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    private const int DAILY_IMPORT_LIMIT = 1;
    private const int MONTHLY_IMPORT_LIMIT = 5;

    public function __construct(
        private ImportService       $importService,
        private MessageBusInterface $messageBus,
        private MediaController     $mediaController,
    )
    {
    }

    #[Route('/imports/upload', methods: 'POST')]
    #[ScopeRequired(Scope::DATA_WRITE)]
    public function upload(
        Newsletter $newsletter,
        Request    $request,
    ): JsonResponse
    {
        $importCounts = $this->importService->getNewsletterImportCounts($newsletter);

        if ($importCounts['month'] >= self::MONTHLY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Monthly import limit reached.');
        }

        if ($importCounts['day'] >= self::DAILY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Daily import limit reached.');
        }

        $file = $request->files->get('file');
        $folder = MediaFolder::IMPORT;

        $upload = $this->mediaController->doUpload($newsletter, $folder, $file);
        $fields = $this->importService->getFields($upload);
        $import = $this->importService->createSubscriberImport($upload, $fields);

        return new JsonResponse(new SubscriberImportObject($import));
    }

    #[Route('/imports/{id}', methods: 'POST')]
    #[ScopeRequired(Scope::DATA_WRITE)]
    public function import(
        Newsletter                       $newsletter,
        SubscriberImport                 $subscriberImport,
        #[MapRequestPayload] ImportInput $input
    ): JsonResponse
    {
        $importCounts = $this->importService->getNewsletterImportCounts($newsletter);

        if ($importCounts['month'] >= self::MONTHLY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Monthly import limit reached.');
        }

        if ($importCounts['day'] >= self::DAILY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Daily import limit reached.');
        }

        if ($subscriberImport->getStatus() !== SubscriberImportStatus::REQUIRES_INPUT) {
            throw new UnprocessableEntityHttpException('Import is not in pending status.');
        }

        $updates = new UpdateSubscriberImportDto();
        $updates->status = SubscriberImportStatus::IMPORTING;
        $updates->fields = $input->mapping;

        $subscriberImport = $this->importService->updateSubscriberImport(
            $subscriberImport,
            $updates
        );

        $this->messageBus->dispatch(new ImportSubscribersMessage($subscriberImport->getId()));

        return new JsonResponse(new SubscriberImportObject($subscriberImport));
    }

    #[Route('/imports', methods: 'GET')]
    #[ScopeRequired(Scope::DATA_READ)]
    public function listImports(Newsletter $newsletter, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 30);
        $offset = $request->query->getInt('offset', 0);

        $imports = $this->importService->getSubscriberImports($newsletter, $limit, $offset);
        $importObjects = array_map(function (SubscriberImport $import) {
            return new SubscriberImportObject($import);
        }, $imports);
        return new JsonResponse($importObjects);
    }

    #[Route('/imports/limits', methods: 'GET')]
    #[ScopeRequired(Scope::DATA_READ)]
    public function importCounts(Newsletter $newsletter): JsonResponse
    {
        $counts = $this->importService->getNewsletterImportCounts($newsletter);

        return new JsonResponse([
            'daily_limit_exceeded' => $counts['day'] >= self::DAILY_IMPORT_LIMIT,
            'monthly_limit_exceeded' => $counts['month'] >= self::MONTHLY_IMPORT_LIMIT,
        ]);
    }
}
