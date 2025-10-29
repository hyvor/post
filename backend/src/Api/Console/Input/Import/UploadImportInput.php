<?php

namespace App\Api\Console\Input\Import;

use Symfony\Component\Validator\Constraints as Assert;

class UploadImportInput
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 1024)]
    public string $source;
}
