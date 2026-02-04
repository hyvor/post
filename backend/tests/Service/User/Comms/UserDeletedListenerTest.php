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

        $newsletters = NewsletterFactory::createMany(2, [
            'organization_id' => 250,
        ]);
        UserFactory::createOne([
            'newsletter_id' => $newsletters[0]->getId(),
            'hyvor_user_id' => $deletingUserId
        ]);
        UserFactory::createOne([
            'newsletter_id' => $newsletters[1]->getId(),
            'hyvor_user_id' => $deletingUserId
        ]);

        $otherNewsletters1 = NewsletterFactory::createMany(3, [
            'organization_id' => 251,
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters1[0]->getId(),
            'hyvor_user_id' => $deletingUserId
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters1[1]->getId(),
            'hyvor_user_id' => $deletingUserId
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters1[2]->getId(),
            'hyvor_user_id' => $deletingUserId
        ]);

        $otherNewsletters2 = NewsletterFactory::createMany(4, [
            'organization_id' => 250,
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters2[0]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters2[1]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters2[2]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $otherNewsletters2[3]->getId(),
        ]);

        $this->getEd()->dispatch(new UserDeleted($deletingUserId));

        $remainingUsers = $this->getEm()->getRepository(User::class)->findAll();
        $this->assertCount(4, $remainingUsers);
    }
}