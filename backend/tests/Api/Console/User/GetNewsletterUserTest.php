<?php

namespace Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use App\Entity\Type\UserRole;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserInviteService::class)]
#[CoversClass(UserObject::class)]
class GetProjectUserTest extends WebTestCase
{
    public function test_get_project_users(): void
    {
        $project = NewsletterFactory::createOne();

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

        $json = $this->getJson();
        /** @var array<int, array<string, mixed>> $json */
        $this->assertCount(1, $json);

        $user = $json[0];
        $this->assertSame('admin', $user['role']);
        $this->assertIsArray($user['user']);
        $this->assertSame('supun', $user['user']['username']);
        $this->assertSame('Supun Wimalasena', $user['user']['name']);
    }
}
