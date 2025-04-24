<?php

namespace App\Api\Public\Object\Form;

use App\Entity\NewsletterList;

class FormListObject
{

    public int $id;
    public int $created_at;
    public string $name;
    public ?string $description;

    public function __construct(NewsletterList $newsletterList)
    {
        $this->id = $newsletterList->getId();
        $this->created_at = $newsletterList->getCreatedAt()->getTimestamp();
        $this->name = $newsletterList->getName();
        $this->description = $newsletterList->getDescription();
    }

}