<?php

namespace App\Tests\Factory;

use App\Entity\SubscriberListUnsubscribed;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SubscriberListUnsubscribed>
 */
final class SubscriberListUnsubscribedFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return SubscriberListUnsubscribed::class;
    }

    /**
     * @return array<mixed>
     */
    protected function defaults(): array
    {
        return [
            'list' => NewsletterListFactory::new(),
            'subscriber' => SubscriberFactory::new(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
