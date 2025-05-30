<?php

namespace App\Tests\Factory;

use App\Entity\SendingProfile;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SendingProfile>
 */
final class SendingProfileFactory extends PersistentProxyObjectFactory
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
        return SendingProfile::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<mixed>
     */
    protected function defaults(): array
    {
        return [
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'newsletter' => NewsletterFactory::createOne(),
            'domain' => null,
            'is_system' => false,
            'is_default' => false,
            'from_name' => self::faker()->name(),
            'from_email' => self::faker()->email(),
            'reply_to_email' => self::faker()->email(),
            'brand_name' => self::faker()->company(),
            'brand_logo' => "https://picsum.photos/200"
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(SendingEmail $sendingEmail): void {})
            ;
    }
}
