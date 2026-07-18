<?php

namespace App\Api\Sudo\Input\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateNewsletterInput
{
    /**
     * Null resets the newsletter to the default daily sending rate.
     */
    #[Assert\Positive]
    public ?int $daily_sending_rate = null;
}
