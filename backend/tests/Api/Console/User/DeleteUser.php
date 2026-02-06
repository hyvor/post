<?php

namespace Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\User;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;

use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserObject::class)]
class DeleteUser extends WebTestCase
{
    public function test_delete_user(): void
    {
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
        ]);

        $user = UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
        ]);

        $userId = $user->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users/' . $user->getId()
        );

        $this->assertResponseStatusCodeSame(200);

        $user = $this->em->getRepository(User::class)->find($userId);
        $this->assertNull($user);
    }

    public function test_delete_user_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 1,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
        ]);

        $user = UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
        ]);

        $userId = $user->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users/' . ($user->getId() + 1)
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
