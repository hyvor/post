<?php

namespace App\Service\Approval\Dto;

use App\Entity\Type\ApprovalStatus;
use App\Util\OptionalPropertyTrait;

class UpdateApprovalDto
{
    use OptionalPropertyTrait;

    public ApprovalStatus $status;
    public string $companyName;
    public string $country;
    public string $website;
    public ?string $socialLinks;
    public ?string $typeOfContent;
    public ?string $frequency;
    public ?string $existingList;
    public ?string $sample;
    public ?string $whyPost;
}
