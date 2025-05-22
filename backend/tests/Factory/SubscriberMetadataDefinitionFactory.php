<?php

namespace App\Tests\Factory;

use App\Entity\SubscriberMetadataDefinition;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SubscriberMetadataDefinition>
 */
final class SubscriberMetadataDefinitionFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return SubscriberMetadataDefinition::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'project' => NewsletterFactory::new(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'key' => self::faker()->text(255),
            'name' => self::faker()->text(255),
            'type' => SubscriberMetadataDefinitionType::TEXT,
        ];
    }

}