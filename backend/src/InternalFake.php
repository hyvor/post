<?php

namespace App;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Component\Component;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
class InternalFake extends \Hyvor\Internal\InternalFake
{

    /**
     * @return array<int, AuthUser|AuthUserArrayPartial>|null
     */
    public function usersDatabase(): ?array
    {
        return [
            [
                'id' => 1,
                'username' => 'supun',
                'name' => 'Supun Wimalasena',
                'current_organization_id' => 1,
            ],
            [
                'id' => 2,
                'username' => 'ishini',
                'name' => 'Ishini Senanayake',
                'current_organization_id' => 1,
            ],
            [
                'id' => 3,
                'username' => 'nadil',
                'name' => 'Nadil Karunaratne',
                'current_organization_id' => 1,
            ],
            [
                'id' => 4,
                'username' => 'thibault',
                'name' => 'Thibault Boutet',
                'current_organization_id' => 1,
            ]
        ];
    }

    public function license(int $organizationId, Component $component): ?License
    {
        return new PostLicense(
            emails: 20000,
            allowRemoveBranding: false
        );
    }

}
