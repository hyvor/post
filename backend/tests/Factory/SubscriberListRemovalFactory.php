<?php

namespace App\Tests\Factory;

use App\Entity\SubscriberListRemoval;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SubscriberListRemoval>
 */
final class SubscriberListRemovalFactory extends PersistentProxyObjectFactory
{
    public function __construct() {}

    public static function class(): string
    {
        return SubscriberListRemoval::class;
    }

    /**
     * @return array<mixed>
     */
    protected function defaults(): array
    {
        return [
            'list' => NewsletterListFactory::new(),
            'subscriber' => SubscriberFactory::new(),
            'reason' => self::faker()->randomElement(['unsubscribe', 'bounce', 'other']),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
