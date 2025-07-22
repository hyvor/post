<?php

namespace App\Api\Console\Input\Approval;

use Symfony\Component\Validator\Constraints as Assert;

class CreateApprovalInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $company_name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $country;

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $website;

    public ?string $social_links;

    public ?string $type_of_content;

    public ?string $frequency;

    public ?string $existing_list;

    public ?string $sample;

    public ?string $why_post;
}
