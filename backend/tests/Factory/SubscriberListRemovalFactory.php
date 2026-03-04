<?php

namespace App\Tests\Factory;

use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<SubscriberListRemoval>
 */
final class SubscriberListRemovalFactory extends PersistentObjectFactory
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
            'reason' => self::faker()->randomElement(ListRemovalReason::cases()),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
