<?php

namespace App\Api\Console\Input\ApiKey;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateApiKeyInput
{
    use OptionalPropertyTrait;

    #[Assert\Length(max: 255)]
    public string $name;

    public bool $is_enabled;

    /**
     * @var string[]
     */
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
