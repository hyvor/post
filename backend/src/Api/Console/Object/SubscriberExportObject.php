<?php

namespace App\Api\Console\Object;

use App\Entity\SubscriberExport;

class SubscriberExportObject
{
    public int $id;
    public string $status;
    public ?string $error_message;
    public ?string $url;
    public int $created_at;

    public function __construct(SubscriberExport $export, ?string $mediaUrl)
    {
        $this->id = $export->getId();
        $this->status = $export->getStatus()->value;
        $this->error_message = $export->getErrorMessage();
        $this->url = $mediaUrl;
        $this->created_at = $export->getCreatedAt()->getTimestamp();
    }
}
