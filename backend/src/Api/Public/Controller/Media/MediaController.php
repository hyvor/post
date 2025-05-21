<?php

namespace App\Api\Public\Controller\Media;

use Symfony\Component\Routing\Attribute\Route;

class MediaController
{

    #[Route('/media/{projectUuid}/{path}', requirements: ['path' => '.+'], methods: 'GET')]
    public function serveMedia(string $projectUuid, string $path): void
    {
    }

}