<?php

namespace App\Tests\Factory;

use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Approval>
 */
final class ApprovalFactory extends PersistentObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return Approval::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
            'status' => ApprovalStatus::REVIEWING,
            'user_id' => self::faker()->randomNumber(),
            'company_name' => self::faker()->company,
            'country' => self::faker()->country,
            'website' => self::faker()->url,
            'social_links' => null,
            'other_info' => null,
            'public_note' => null,
            'private_note' => null,
            'organization_id' => self::faker()->randomNumber(),
        ];
    }

}
