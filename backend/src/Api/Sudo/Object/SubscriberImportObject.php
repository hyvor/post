<?php

namespace App\Api\Sudo\Object;

use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;

class SubscriberImportObject
{
    public int $id;
    public int $created_at;
    public string $newsletter_subdomain;
    public SubscriberImportStatus $status;
    public int $total_rows;
    public string $source;
    /** @var string[] */
    public array $columns;

    public function __construct(SubscriberImport $subscriberImport)
    {
        $this->id = $subscriberImport->getId();
        $this->created_at = $subscriberImport->getCreatedAt()->getTimestamp();
        $this->newsletter_subdomain = $subscriberImport->getNewsletter()->getSubdomain();
        $this->status = $subscriberImport->getStatus();
        $this->total_rows = $subscriberImport->getCsvRows() ?? 0;
        $this->source = $subscriberImport->getSource();
        $this->columns = $subscriberImport->getCsvFields() ?? [];
    }
}