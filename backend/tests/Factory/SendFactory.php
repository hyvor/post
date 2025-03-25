<?php

namespace App\Tests\Factory;

use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Send>
 */
final class SendFactory extends PersistentProxyObjectFactory
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
        return Send::class;
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
            'issue' => IssueFactory::new(),
            'subscriber' => SubscriberFactory::new(),
            'email' => self::faker()->email(),
            'status' => SendStatus::PENDING,
            'error_private' => self::faker()->text(255),
            'failed_tries' => self::faker()->numberBetween(0, 10),
            'open_count' => self::faker()->numberBetween(0, 10),
            'click_count' => self::faker()->numberBetween(0, 10),
            'hard_bounce' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Send $issue): void {})
        ;
    }
}
