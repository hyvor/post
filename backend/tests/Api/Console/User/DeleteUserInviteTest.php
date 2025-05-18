<?php

namespace Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\UserInvite;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserInviteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserInviteService::class)]
#[CoversClass(UserObject::class)]
class DeleteUserInviteTest extends WebTestCase
{
    public function test_delete_user_invite(): void
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

        $inviteId = $invite->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/invites/' . $invite->getId()
        );

        $this->assertResponseStatusCodeSame(200);

        $invite = $this->em->getRepository(UserInvite::class)->find($inviteId);
        $this->assertNull($invite);
    }

    public function test_delete_user_invite_not_found(): void
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

        $inviteId = $invite->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/invites/' . ($inviteId + 1)
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
