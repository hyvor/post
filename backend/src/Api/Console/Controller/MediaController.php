<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Media\MediaUploadInput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MediaController extends AbstractController
{

    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/media', methods: 'POST')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        $input = new MediaUploadInput();
        $input->file = $file;

        $errors = $this->validator->validate($input);

        if (count($errors) > 0) {
            throw new ValidationFailedException(
                'Invalid file upload',
                $errors
            );
        }

        return $this->json([]);
    }

}