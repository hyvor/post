<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Media\MediaUploadInput;
use App\Api\Console\Object\MediaObject;
use App\Entity\Media;
use App\Entity\Newsletter;
use App\Entity\Type\MediaFolder;
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
        private MediaService       $mediaService
    )
    {
    }

    public function doUpload(
        Newsletter $newsletter,
        MediaFolder $folder,
        mixed $file,
        int $maxSizeMb = 10,
    ): Media
    {

        $constraint = new Constraints\File(
            maxSize: $maxSizeMb . 'M',
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
            return $this->mediaService->upload(
                $newsletter,
                $folder,
                $file
            );
        } catch (MediaUploadException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

    }

    #[Route('/media', methods: 'POST')]
    #[ScopeRequired(Scope::MEDIA_WRITE)]
    public function upload(
        Newsletter                            $newsletter,
        Request                               $request,
        #[MapRequestPayload] MediaUploadInput $input
    ): JsonResponse
    {
        $file = $request->files->get(key: 'file');
        $folder = $input->folder;

        $media = $this->doUpload(
            $newsletter,
            $folder,
            $file,
        );

        return $this->json(
            new MediaObject(
                $media,
                $this->mediaService->getPublicUrl($media)
            )
        );
    }

}
