<?php

namespace Api\Console\UserInvite;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use Hyvor\Internal\Auth\AuthFake;

class InviteUserTest extends WebTestCase
{
    public function test_invite_user(): void
    {
        $project = ProjectFactory::createOne();

        AuthFake::generateUser([
                [
                    'id' => 11,
                    'username' => 'test',
                    'name' => 'Test User',
                    'email' => 'test@hyvor.com'
                ]
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/invite',
            [
                'username' => $user->username,
            ]
        );

        $this->assertResponseStatusCodeSame(200);
    }
}
