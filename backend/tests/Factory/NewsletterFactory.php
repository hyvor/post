<?php

namespace App\Tests\Factory;

use App\Entity\Meta\NewsletterMeta;
use App\Entity\Newsletter;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Newsletter>
 */
final class NewsletterFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Newsletter::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'subdomain' => self::faker()->userName(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name' => self::faker()->text(255),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'user_id' => self::faker()->randomNumber(),
            'meta' => new NewsletterMeta(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Newsletter $newsletter): void {})
            ;
    }
}
