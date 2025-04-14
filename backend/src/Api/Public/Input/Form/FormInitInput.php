<?php

namespace App\Api\Public\Input\Form;

use Symfony\Component\Validator\Constraints as Assert;

class FormInitInput
{

    #[Assert\NotBlank]
    public string $project_uuid;

    public ?string $language = null;

    /**
     * Prefill the email field with the email of the user
     */
    public ?string $email = null;

    /**
     * @var null|array<int>
     */
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public ?array $list_ids = null;

}