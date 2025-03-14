<?php

namespace App\Tests\Factory;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Issue>
 */
final class IssueFactory extends PersistentProxyObjectFactory
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
        return Issue::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<mixed>
     */
    protected function defaults(): array
    {
        return [
            'project' => ProjectFactory::new(),
            'content' => self::faker()->text(255),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'error_private' => self::faker()->text(255),
            'failed_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'from_email' => self::faker()->email(),
            'from_name' => self::faker()->text(255),
            'html' => self::faker()->text(),
            'reply_to_email' => self::faker()->email(),
            'scheduled_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sending_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sent_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status' => self::faker()->randomElement(IssueStatus::cases()),
            'subject' => self::faker()->text(255),
            'text' => self::faker()->text(),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'uuid' => self::faker()->text(255),
            'list_ids' => []
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Issue $issue): void {})
        ;
    }
}
