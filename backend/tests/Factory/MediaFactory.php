<?php

namespace App\Tests\Factory;

use App\Entity\Issue;
use App\Entity\Media;
use App\Entity\Type\IssueStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Media>
 */
final class MediaFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Media::class;
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
            'extension' => self::faker()->text(255),
            'file' => self::faker()->text(255),
            'size' => self::faker()->randomNumber(),
            'type' => self::faker()->randomElement(['image/jpeg', 'image/png']),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

}