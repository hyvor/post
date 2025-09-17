<?php

namespace App\Tests\Factory;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use Symfony\Component\Uid\Uuid;
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
        $total = self::faker()->randomNumber() + 1;

        return [
            'uuid' => Uuid::v4(),
            'newsletter' => NewsletterFactory::new(),
            'content' => (string)json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => self::faker()->text(),
                            ],
                        ],
                    ],
                ],
            ]),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sending_profile' => SendingProfileFactory::new(),
            'error_private' => self::faker()->text(255),
            'failed_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'html' => self::faker()->text(),
            'scheduled_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sending_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sent_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status' => self::faker()->randomElement(IssueStatus::cases()),
            'subject' => self::faker()->text(70),
            'text' => self::faker()->text(),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'list_ids' => [],
            'total_sends' => $total,
            'ok_sends' => self::faker()->randomNumber(),
            'failed_sends' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Issue $issue): void {})
            ;
    }
}
