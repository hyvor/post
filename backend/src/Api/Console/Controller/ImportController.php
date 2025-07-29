<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Import\ImportInput;
use App\Api\Console\Object\SubscriberImportObject;
use App\Entity\Newsletter;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Media\MediaService;
use App\Service\Media\MediaUploadException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private MediaService $mediaService,
        private ImportService $importService,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/imports/upload', methods: 'POST')]
    public function upload(
        Newsletter $newsletter,
        Request $request,
    ): JsonResponse
    {
        $file = $request->files->get('file');
        $folder = MediaFolder::IMPORT;

        $constraint = new Constraints\File(
            maxSize: '100M',
            extensions: $folder->getAllowedExtensions()
        );
        $errors = $this->validator->validate($file, $constraint);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException(
                previous: new ValidationFailedException(
                    'Invalid file upload',
                    $errors
                )
            );
        }

        assert($file instanceof UploadedFile);

        try {
            $upload = $this->mediaService->upload(
                $newsletter,
                $folder,
                $file
            );
        } catch (MediaUploadException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $fields = $this->importService->getFields($upload);
        $import = $this->importService->createSubscriberImport($upload, $fields);

        return new JsonResponse(new SubscriberImportObject($import));
    }

    #[Route('/imports/{id}', methods: 'POST')]
    public function import(
        SubscriberImport $subscriberImport,
        #[MapRequestPayload] ImportInput $input
    ): JsonResponse
    {
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
}
