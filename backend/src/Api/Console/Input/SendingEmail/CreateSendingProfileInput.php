<?php

namespace App\Api\Console\Input\SendingEmail;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSendingProfileInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    public string $from_email;

    #[Assert\Length(max: 255)]
    #[Assert\Type('string')]
    public ?string $from_name;

    #[Assert\Length(max: 255)]
    #[Assert\Email]
    public ?string $reply_to_email;

    #[Assert\Length(max: 255)]
    #[Assert\Type('string')]
    public ?string $brand_name;

    #[Assert\Length(max: 1024)]
    #[Assert\Url]
    public ?string $brand_logo;
}
