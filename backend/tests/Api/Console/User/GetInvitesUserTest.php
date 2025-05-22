<?php

namespace Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\UserInvite;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserInviteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserInviteService::class)]
#[CoversClass(UserObject::class)]
#[CoversClass(UserInvite::class)]
class GetInvitesUserTest extends WebTestCase
{
    public function test_get_project_invites(): void
    {
        $project = NewsletterFactory::createOne();

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
            '/invites'
        );

        $this->assertResponseStatusCodeSame(200);
        /** @var array<int, array<string, array<string, mixed>>> $json */
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertSame('admin', $json[0]['role']);
        $this->assertSame('supun', $json[0]['user']['username']);
    }
}
