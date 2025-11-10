<?php

namespace App\Api\Sudo\Input\Approval;

use App\Entity\Type\ApprovalStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ApproveInput
{
    #[Assert\NotBlank]
    public ApprovalStatus $status;

    public ?string $public_note = null;

    public ?string $private_note = null;
}
