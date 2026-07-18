<?php

namespace App\Api\Console\Input\ApiKey;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Symfony\Component\Validator\Constraints as Assert;

class CreateApiKeyInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    /**
     * @var string[]
     */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Choice(callback: 'getScopeValues'),
    ])]
    public array $scopes;

    /**
     * @return string[]
     */
    public static function getScopeValues(): array
    {
        return array_column(PostScope::cases(), 'value');
    }
}
