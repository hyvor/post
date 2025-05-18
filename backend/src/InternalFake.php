<?php

namespace App;

use Hyvor\Internal\Auth\AuthUser;

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
            ],
            [
                'id' => 2,
                'username' => 'ishini',
                'name' => 'Ishini Senanayake',
            ],
            [
                'id' => 3,
                'username' => 'nadil',
                'name' => 'Nadil Karunaratne',
            ],
            [
                'id' => 4,
                'username' => 'thibault',
                'name' => 'Thibault Boutet',
            ]
        ];
    }

}