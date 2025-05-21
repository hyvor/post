<?php

namespace App\Api\Console\Input\Media;

use App\Service\Media\MediaUploadTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

class MediaUploadInput
{

    #[Assert\NotBlank]
    public MediaUploadTypeEnum $type;

}