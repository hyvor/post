<?php

namespace Api\Console\User;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserInviteFactory;
use Hyvor\Internal\Auth\AuthFake;

class GetInvitesUserTest extends WebTestCase
{
    public function test_get_project_invites(): void
    {
        $project = ProjectFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
        ]);

        $invite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'project' => $project,
        ]);


        $response = $this->consoleApi(
            $project,
            'GET',
            '/users/invites'
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('admin', $json[0]['role']);
        $this->assertSame('supun', $json[0]['user']['username']);
    }
}
