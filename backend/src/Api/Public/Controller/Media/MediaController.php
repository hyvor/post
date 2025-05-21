<?php

namespace App\Api\Public\Controller\Media;

use App\Service\Media\MediaReadException;
use App\Service\Media\MediaService;
use App\Service\Project\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService,
        private MediaService $mediaService
    ) {
    }

    #[Route('/media/{projectUuid}/{path}', requirements: ['path' => '.+'], methods: 'GET')]
    public function serveMedia(string $projectUuid, string $path): Response
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if ($project === null) {
            throw new NotFoundHttpException('Project not found');
        }

        $media = $this->mediaService->getMediaByPath($project, $path);

        if ($media === null) {
            throw new NotFoundHttpException('Media not found');
        }

        if ($media->isPrivate()) {
            throw new UnauthorizedHttpException('Not authorized to access this media');
        }

        $stream = $this->mediaService->getMediaStream($media);

        return new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $this->mediaService->getMimeType($media->getExtension()),
            'Content-Length' => $media->getSize(),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

}