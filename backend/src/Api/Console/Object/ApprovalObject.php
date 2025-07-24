<?php

namespace App\Api\Console\Object;

use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;

class ApprovalObject
{
    public ?int $id = null;
    public ?int $created_at = null;
    public ApprovalStatus $status;
    public ?string $company_name = null;
    public ?string $country = null;
    public ?string $website = null;
    public ?string $social_links = null;
    public ?string $type_of_content = null;
    public ?string $frequency = null;
    public ?string $existing_list = null;
    public ?string $sample = null;
    public ?string $why_post = null;

    public function __construct(Approval $approval)
    {

        $this->id = $approval->getId();
        $this->created_at = $approval->getCreatedAt()->getTimestamp();
        $this->status = $approval->getStatus();
        $this->company_name = $approval->getCompanyName();
        $this->country = $approval->getCountry();
        $this->website = $approval->getWebsite();
        $this->social_links = $approval->getSocialLinks();

        $otherInfo = $approval->getOtherInfo();

        if ($otherInfo) {
            $this->type_of_content = $otherInfo['type_of_content'] ?? null;
            $this->frequency = $otherInfo['frequency'] ?? null;
            $this->existing_list = $otherInfo['existing_list'] ?? null;
            $this->sample = $otherInfo['sample'] ?? null;
            $this->why_post = $otherInfo['why_post'] ?? null;
        }
    }
}
