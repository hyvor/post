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

    public function test_invite_user_by_email(): void
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
                'email' => 'supun@hyvor.com',
            ]
        );

        $json = $this->getJson($response);
        $this->assertSame('admin', $json['role']);

        $user = $json['user'];
        $this->assertIsArray($user);
        $this->assertSame('supun@hyvor.com', $user['email']);
        $this->assertSame('Supun Wimalasena', $user['name']);
    }

    public function test_invite_user_by_email_with_wrong_email(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/users/invites',
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson($response);
        $this->assertSame('User does not exists', $json['message']);
    }

    public function test_invite_user_by_email_with_wrong_username(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/users/invites',
            [
                'username' => 'supun',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson($response);
        $this->assertSame('User does not exists', $json['message']);
    }
}
