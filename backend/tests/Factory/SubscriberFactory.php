<?php

namespace App\Tests\Factory;

use App\Entity\Subscriber;
use App\Enum\SubscriberSource;
use App\Enum\SubscriberStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Subscriber>
 */
final class SubscriberFactory extends PersistentProxyObjectFactory
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
        return Subscriber::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'email' => self::faker()->text(255),
            'project' => ProjectFactory::new(),
            'source' => self::faker()->randomElement(SubscriberSource::cases()),
            'source_id' => self::faker()->randomNumber(),
            'status' => self::faker()->randomElement(SubscriberStatus::cases()),
            'subscribe_ip' => self::faker()->text(255),
            'subscribed_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'unsubscribe_reason' => self::faker()->text(255),
            'unsubscribed_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Subscriber $subscriber): void {})
        ;
    }
}
