<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Media\MediaUploadInput;
use App\Api\Console\Object\MediaObject;
use App\Entity\Newsletter;
use App\Service\Media\MediaService;
use App\Service\Media\MediaUploadException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints;

class MediaController extends AbstractController
{

    public function __construct(
        private ValidatorInterface $validator,
        private MediaService $mediaService
    ) {
    }

    #[Route('/media', methods: 'POST')]
    public function upload(
        Newsletter $project,
        Request $request,
        #[MapRequestPayload] MediaUploadInput $input
    ): JsonResponse {
        $file = $request->files->get('file');
        $folder = $input->folder;

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
            $media = $this->mediaService->upload(
                $project,
                $folder,
                $file
            );
        } catch (MediaUploadException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $this->json(
            new MediaObject(
                $media,
                $this->mediaService->getPublicUrl($media)
            )
        );
    }

}