<?php

namespace App\Api\Console\Input\Issue;

use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateIssueInput
{
    use OptionalPropertyTrait;

    public ?string $subject;
    /**
     * @var array<int>
     */
    #[Assert\Count(min: 1, minMessage: "There should be at least one list.")]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $lists;
    public ?string $content;
    public ?int $sending_profile_id;
}
