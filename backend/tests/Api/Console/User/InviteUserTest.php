<?php

namespace App\Tests\Api\Console\User;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use Hyvor\Internal\Auth\AuthFake;


class InviteUserTest extends WebTestCase
{
    public function test_invite_user_by_username(): void
    {
        $project = ProjectFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/users/invites',
            [
                'username' => 'supun',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson($response);
        $this->assertSame('admin', $json['role']);

        $user = $json['user'];
        $this->assertIsArray($user);
        $this->assertSame('supun', $user['username']);
        $this->assertSame('Supun Wimalasena', $user['name']);
    }

    // more tests: by email, wrong username/email, cover everything
}
