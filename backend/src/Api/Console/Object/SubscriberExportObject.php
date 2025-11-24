<?php

namespace App\Api\Console\Object;

use App\Entity\SubscriberExport;
use App\Entity\Type\SubscriberExportStatus;

class SubscriberExportObject
{
    public int $id;
    public int $created_at;
    public SubscriberExportStatus $status;
    public ?string $error_message;
    public ?string $url;

    public function __construct(SubscriberExport $export, ?string $mediaUrl)
    {
        $this->id = $export->getId();
        $this->created_at = $export->getCreatedAt()->getTimestamp();
        $this->status = $export->getStatus();
        $this->error_message = $export->getErrorMessage();
        $this->url = $mediaUrl;
    }
}