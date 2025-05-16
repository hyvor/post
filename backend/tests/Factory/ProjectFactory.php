<?php

namespace App\Tests\Factory;

use App\Entity\Meta\ProjectMeta;
use App\Entity\Project;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class ProjectFactory extends PersistentProxyObjectFactory
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
        return Project::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name' => self::faker()->text(255),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'user_id' => self::faker()->randomNumber(),
            'meta' => new ProjectMeta(),
            'default_email_username' => self::faker()->text(255)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Project $project): void {})
        ;
    }
}
