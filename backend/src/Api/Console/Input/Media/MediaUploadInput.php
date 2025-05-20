<?php

namespace App\Api\Console\Input\Media;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class MediaUploadInput
{

    #[Assert\NotBlank]
    #[Assert\File(
        maxSize: '100M',
        extensions: [

            // images
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',

            // csv (imports)
            'csv',

        ],
    )]
    public mixed $file;

}