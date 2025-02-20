<?php

namespace App\Api\Console\Object;

use App\Entity\NewsletterList;

class ListObject
{

    public int $id;
    public int $created_at; // unix timestamp

    public string $name;
    public int $project_id;


    public function __construct(NewsletterList $newsletterList)
    {
        $this->id = $newsletterList->getId();
        $this->created_at = $newsletterList->getCreatedAt()->getTimestamp();
        $this->name = $newsletterList->getName();
        $this->project_id = $newsletterList->getProject()->getId();
    }
}
