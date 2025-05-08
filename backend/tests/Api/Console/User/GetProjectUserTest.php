<?php

namespace Api\Console\User;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserFactory;
use App\Entity\Type\UserRole;
use Hyvor\Internal\Auth\AuthFake;

class GetProjectUserTest extends WebTestCase
{
    public function test_get_project_users(): void
    {
        $project = ProjectFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::ADMIN
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/users'
        );

        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson($response);
        $this->assertCount(1, $json);

        $user = $json[0];
        $this->assertIsArray($user);
        $this->assertSame('admin', $user['role']);
        $this->assertIsArray($user['user']);
        $this->assertSame('supun', $user['user']['username']);
        $this->assertSame('Supun Wimalasena', $user['user']['name']);
    }
}
