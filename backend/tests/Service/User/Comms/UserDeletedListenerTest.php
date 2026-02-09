<?php

namespace App\Tests\Service\User\Comms;

use App\Entity\User;
use App\Service\User\Comms\UserDeletedListener;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\User\UserDeleted;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDeletedListener::class)]
class UserDeletedListenerTest extends WebTestCase
{
    public function test_delete_users(): void
    {
        $deletingUserId = 12345;

        $deletingUsers1 = UserFactory::createMany(2, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingUserId
        ]);
        $deletingUserId1 = $deletingUsers1[0]->getId();

        $deletingUsers2 = UserFactory::createMany(3, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingUserId
        ]);
        $deletingUserId2 = $deletingUsers2[0]->getId();

        $nonDeletingUsers = UserFactory::createMany(4, [
            'newsletter' => NewsletterFactory::createOne()
        ]);
        $nonDeletingUserId = $nonDeletingUsers[0]->getId();

        $this->getEd()->dispatch(new UserDeleted($deletingUserId));

        $userRepository = $this->getEm()->getRepository(User::class);

        $remainingUsers = $userRepository->findAll();
        $this->assertCount(4, $remainingUsers);

        $this->assertNull($userRepository->findOneBy(['id' => $deletingUserId1]));
        $this->assertNull($userRepository->findOneBy(['id' => $deletingUserId2]));
        $this->assertNotNull($userRepository->findOneBy(['id' => $nonDeletingUserId]));
    }
}