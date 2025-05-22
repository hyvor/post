<?php

namespace App\Tests\Factory;

use App\Entity\Media;
use App\Entity\Type\MediaFolder;
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
            'uuid' => self::faker()->uuid(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'project' => NewsletterFactory::new(),
            'folder' => self::faker()->randomElement(MediaFolder::cases()),
            'extension' => self::faker()->text(255),
            'size' => self::faker()->randomNumber(),
            'original_name' => self::faker()->text(15),
            'is_private' => false,
        ];
    }

}