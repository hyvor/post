<?php

namespace App\Api\Console\Object;

use App\Entity\Approval;

class ApprovalObject
{

//export type Approval = {
//is_approved: boolean;
//id: number | null;
//created_at: number | null;
//company_name: string | null;
//country: string | null;
//website: string | null;
//social_links: string | null;
//type_of_content: string | null;
//frequency: string | null;
//existing_list: string | null;
//sample: string | null;
//why_post: string | null;
//}

    public bool $is_approved;
    public ?int $id = null;
    public ?int $created_at = null;
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
        $this->is_approved = $approval->isApproved();

        if (!$this->is_approved) {

            $this->id = $approval->getId();
            $this->created_at = $approval->getCreatedAt()->getTimestamp();
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


}
