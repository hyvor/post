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
class DeleteUserInviteTest extends WebTestCase
{
    public function test_delete_user_invite(): void
    {
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
        ]);

        $invite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
        ]);

        $inviteId = $invite->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/invites/' . $invite->getId()
        );

        $this->assertResponseStatusCodeSame(200);

        $invite = $this->em->getRepository(UserInvite::class)->find($inviteId);
        $this->assertNull($invite);
    }

    public function test_delete_user_invite_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
        ]);

        $invite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
        ]);

        $inviteId = $invite->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/invites/' . ($inviteId + 1)
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
