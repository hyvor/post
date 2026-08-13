<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\Import\ImportInput;
use App\Api\Console\Input\Import\UploadImportInput;
use App\Api\Console\Object\SubscriberImportObject;
use App\Entity\Newsletter;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class ImportController extends AbstractController
{
    public function __construct(
        private ImportService $importService,
        private MessageBusInterface $messageBus,
        private MediaController $mediaController,
    ) {}

    #[Route('/imports/upload', methods: 'POST')]
    #[ScopeRequired(PostScope::DATA_WRITE)]
    #[OA\Post(
        description: 'Uploads a CSV file of subscribers to import. Returns the parsed fields and row count so that ' .
            'the fields can be mapped before starting the import.',
        summary: 'Upload a subscriber import file',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created subscriber import object.',
        content: new Model(type: SubscriberImportObject::class),
    )]
    public function upload(
        Newsletter $newsletter,
        Request $request,
        #[MapRequestPayload] UploadImportInput $input,
    ): JsonResponse {
        $importCounts = $this->importService->getNewsletterImportCounts($newsletter);

        if ($importCounts['month'] >= ImportService::MONTHLY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Monthly import limit reached.');
        }

        if ($importCounts['day'] >= ImportService::DAILY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Daily import limit reached.');
        }

        $file = $request->files->get('file');
        $folder = MediaFolder::IMPORT;

        $upload = $this->mediaController->doUpload($newsletter, $folder, $file);
        $fields = $this->importService->getFields($upload);
        $rowCount = $this->importService->getRowCount($upload);
        $import = $this->importService->createSubscriberImport($upload, $input->source, $fields, $rowCount);

        return new JsonResponse(new SubscriberImportObject($import));
    }

    #[Route('/imports/{id}', methods: 'POST')]
    #[ScopeRequired(PostScope::DATA_WRITE)]
    #[OA\Post(
        description: 'Starts an import using the field mapping provided for a previously uploaded subscriber import file.',
        summary: 'Start a subscriber import',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated subscriber import object.',
        content: new Model(type: SubscriberImportObject::class),
    )]
    public function start(
        Newsletter $newsletter,
        SubscriberImport $subscriberImport,
        #[MapRequestPayload] ImportInput $input,
    ): JsonResponse {
        $importCounts = $this->importService->getNewsletterImportCounts($newsletter);

        if ($importCounts['month'] >= ImportService::MONTHLY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Monthly import limit reached.');
        }

        if ($importCounts['day'] >= ImportService::DAILY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Daily import limit reached.');
        }

        if ($subscriberImport->getStatus() !== SubscriberImportStatus::REQUIRES_INPUT) {
            throw new UnprocessableEntityHttpException('Import is not in pending status.');
        }

        $updates = new UpdateSubscriberImportDto();
        $updates->fields = $input->mapping;

        if (($subscriberImport->getCsvRows() ?? 0) < ImportService::SUBSCRIBER_LIMIT_FOR_MANUAL_REVIEW) {
            $updates->status = SubscriberImportStatus::IMPORTING;
            $this->messageBus->dispatch(new ImportSubscribersMessage($subscriberImport->getId()));
        } else {
            $updates->status = SubscriberImportStatus::PENDING_APPROVAL;
        }

        $subscriberImport = $this->importService->updateSubscriberImport(
            $subscriberImport,
            $updates,
        );

        return new JsonResponse(new SubscriberImportObject($subscriberImport));
    }

    #[Route('/imports', methods: 'GET')]
    #[ScopeRequired(PostScope::DATA_READ)]
    #[OA\Get(
        description: 'Get all subscriber imports of the newsletter.',
        summary: 'Get subscriber imports',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of subscriber imports',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SubscriberImportObject::class)),
        ),
    )]
    public function list(Newsletter $newsletter, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 30);
        $offset = $request->query->getInt('offset', 0);

        $imports = $this->importService->getSubscriberImports($newsletter, limit: $limit, offset: $offset);
        $importObjects = array_map(function (SubscriberImport $import) {
            return new SubscriberImportObject($import);
        }, $imports);
        return new JsonResponse($importObjects);
    }

    #[Route('/imports/limits', methods: 'GET')]
    #[ScopeRequired(PostScope::DATA_READ)]
    #[OA\Get(
        description: 'Get whether the newsletter has reached its daily or monthly subscriber import limits.',
        summary: 'Get subscriber import limits',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns whether the daily and monthly import limits have been exceeded.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'daily_limit_exceeded', type: 'boolean'),
                new OA\Property(property: 'monthly_limit_exceeded', type: 'boolean'),
            ],
        ),
    )]
    public function getLimits(Newsletter $newsletter): JsonResponse
    {
        $counts = $this->importService->getNewsletterImportCounts($newsletter);

        return new JsonResponse([
            'daily_limit_exceeded' => $counts['day'] >= ImportService::DAILY_IMPORT_LIMIT,
            'monthly_limit_exceeded' => $counts['month'] >= ImportService::MONTHLY_IMPORT_LIMIT,
        ]);
    }
}
