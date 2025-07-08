<?php

namespace App\Api\Console\Input\SendingEmail;

use App\Api\Console\Normalizer\NormalizeEmptyToNull;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Validator\Constraints as Assert;

#[NormalizeEmptyToNull]
#[Context(['empty_strings_to_null' => true])]
class CreateSendingProfileInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    #[Context(['empty_strings_to_null' => true])]
    public string $from_email;

    #[Assert\Length(max: 255)]
    #[Assert\Type('string')]
    public ?string $from_name = null;

    #[Assert\Length(max: 255)]
    #[Assert\Email]
    public ?string $reply_to_email = null;

    #[Assert\Length(max: 255)]
    #[Assert\Type('string')]
    public ?string $brand_name = null;

    #[Assert\Length(max: 1024)]
    #[Assert\Url]
    public ?string $brand_logo = null;
}
