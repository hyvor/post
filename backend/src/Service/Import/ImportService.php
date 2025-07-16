<?php

namespace App\Service\Import;

use App\Entity\Media;
use App\Service\Media\MediaService;

class ImportService
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * @return array<int, string>
     */
    public function getFields(Media $media): array
    {
        $stream = $this->mediaService->getMediaStream($media);

        if (!is_resource($stream)) {
            throw new ImportException("Invalid CSV stream.");
        }

        $headers = fgetcsv($stream);
        fclose($stream);

        if ($headers === false) {
            throw new ImportException("Could not read CSV headers.");
        }

        return $headers;
    }

}
