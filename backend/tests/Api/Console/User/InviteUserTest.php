<?php

namespace App\Tests\Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\Type\UserRole;
use App\Entity\UserInvite;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserInviteFactory;
use Hyvor\Internal\Auth\AuthFake;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\CoversClass;


#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserInviteService::class)]
#[CoversClass(UserObject::class)]
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
            '/invites',
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
            '/invites',
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

        $userInviteRepository = $this->em->getRepository(UserInvite::class);
        $userInvite = $userInviteRepository->findOneBy([
            'hyvor_user_id' => 15,
        ]);
        $this->assertNotNull($userInvite);
        $this->assertSame($project->getId(), $userInvite->getProject()->getId());
        $this->assertSame(15, $userInvite->getHyvorUserId());
        $this->assertSame(new \DateTimeImmutable()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'), $userInvite->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function test_invite_user_by_email_with_wrong_email(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/invites',
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
            '/invites',
            [
                'username' => 'supun',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson($response);
        $this->assertSame('User does not exists', $json['message']);
    }

    public function test_invite_existing_user(): void
    {
        $project = ProjectFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $expirationDate = new \DateTimeImmutable();

        UserInviteFactory::createOne([
            'hyvor_user_id' => 15,
            'project' => $project,
            'expires_at' => $expirationDate,
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/invites',
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson($response);
        $this->assertSame('admin', $json['role']);
        $this->assertSame($expirationDate->add(new \DateInterval('P1D'))->getTimestamp(), $json['expires_at']);

    }
}
