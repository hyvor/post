<?php

namespace App\Api\Public\Input\Form;

use Symfony\Component\Validator\Constraints as Assert;

class FormSubscribeInput
{

    #[Assert\NotBlank]
    public int $project_id;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    /**
     * @var int[] $list_ids
     */
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $list_ids;

}