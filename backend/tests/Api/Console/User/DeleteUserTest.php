<?php

namespace Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\User;
use App\Service\User\UserService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;

use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserObject::class)]
class DeleteUserTest extends WebTestCase
{
    public function test_delete_user_by_id(): void
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

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users',
            ['id' => $userId]
        );

        $this->assertResponseStatusCodeSame(200);

        $user = $this->em->getRepository(User::class)->find($userId);
        $this->assertNull($user);
    }

    public function test_delete_user_by_user_id(): void
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

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users',
            ['user_id' => 1]
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

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users',
            ['id' => $user->getId() + 1]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_user_missing_input(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/users',
            []
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
