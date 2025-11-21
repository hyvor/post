<?php

namespace App\Api\Console\Input\SendingProfile;

use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;


class UpdateSendingProfileInput
{
    use OptionalPropertyTrait;

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

    #[Assert\Url]
    public ?string $brand_logo;

    #[Assert\Url]
    public ?string $brand_url;

    #[Assert\IsTrue]
    public bool $is_default;
}
