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

        UserFactory::createMany(2, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingUserId
        ]);
        UserFactory::createMany(3, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingUserId
        ]);
        UserFactory::createMany(4, [
            'newsletter' => NewsletterFactory::createOne()
        ]);

        $this->getEd()->dispatch(new UserDeleted($deletingUserId));

        $remainingUsers = $this->getEm()->getRepository(User::class)->findAll();
        $this->assertCount(4, $remainingUsers);
    }
}