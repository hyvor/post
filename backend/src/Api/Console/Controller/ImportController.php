<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Import\ImportInput;
use App\Api\Console\Object\SubscriberImportFieldObject;
use App\Entity\Newsletter;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Media\MediaService;
use App\Service\Media\MediaUploadException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private MediaService $mediaService,
        private ImportService $importService
    ) {
    }

    #[Route('/subscribers/import/upload', methods: 'POST')]
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
        $import = $this->importService->createSubscriberImport($upload);

        return new JsonResponse(new SubscriberImportFieldObject($import, $fields));
    }

    #[Route('/subscribers/import/{id}', methods: 'POST')]
    public function import(
        Newsletter $newsletter,
        int $id,
        #[MapRequestPayload] ImportInput $input
    ): JsonResponse
    {
        $subscriberImport = $this->importService->getPendingImportOfNewsletter($newsletter, $id);

        if ($subscriberImport === null) {
            throw new UnprocessableEntityHttpException('No pending import found.');
        }

        $updates = new UpdateSubscriberImportDto();
        $updates->status = SubscriberImportStatus::IMPORTING;
        $updates->fields = $input->mapping;

        $subscriberImport = $this->importService->updateSubscriberImport(
            $subscriberImport,
            $updates
        );

        dd($subscriberImport);
        return new JsonResponse('Import subscribers from csv');
    }

    #[Route('/subscribers/import', methods: 'GET')]
    public function listImports(): JsonResponse
    {
        // TODO
        return new JsonResponse('List imports');
    }
}
