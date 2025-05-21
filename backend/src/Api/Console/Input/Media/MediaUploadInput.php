<?php

namespace App\Api\Console\Input\Media;

use App\Entity\Type\MediaFolder;
use Symfony\Component\Validator\Constraints as Assert;

class MediaUploadInput
{

    #[Assert\NotBlank]
    public MediaFolder $folder;

}