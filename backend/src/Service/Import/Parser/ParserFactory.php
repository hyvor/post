<?php

namespace App\Service\Import\Parser;

use App\Service\Media\MediaService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;

class ParserFactory
{

    public function __construct(
        private MediaService $mediaService,
        private SubscriberMetadataService $subscriberMetadataService,
    )
    {}

    public function csv(): CsvParser
    {
        return new CsvParser($this->mediaService, $this->subscriberMetadataService);
    }

}