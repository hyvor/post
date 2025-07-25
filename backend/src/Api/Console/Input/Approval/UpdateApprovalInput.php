<?php

namespace App\Api\Console\Input\Approval;

use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateApprovalInput
{
    use OptionalPropertyTrait;

    #[Assert\Length(max: 255)]
    public string $company_name;

    #[Assert\Length(max: 255)]
    public string $country;

    #[Assert\Url]
    public string $website;

    public ?string $social_links;

    public ?string $type_of_content;

    public ?string $frequency;

    public ?string $existing_list;

    public ?string $sample;

    public ?string $why_post;
}
