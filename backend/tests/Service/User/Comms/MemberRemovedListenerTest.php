<?php

namespace App\Tests\Service\User\Comms;

use App\Entity\User;
use App\Service\User\Comms\MemberRemovedListener;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MemberRemovedListener::class)]
class MemberRemovedListenerTest extends WebTestCase
{
    public function test_delete_users(): void
    {
        $deletingMemberUserId = 12345;
        $deletingMemberOrganizationId = 1;

        UserFactory::createMany(2, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingMemberUserId,
            'organization_id' => $deletingMemberOrganizationId,
        ]);
        UserFactory::createMany(3, [
            'newsletter' => NewsletterFactory::new(),
            'hyvor_user_id' => $deletingMemberUserId,
            'organization_id' => 2,
        ]);
        UserFactory::createMany(4, [
            'newsletter' => NewsletterFactory::createOne(),
            'organization_id' => $deletingMemberOrganizationId
        ]);

        $this->getEd()->dispatch(new MemberRemoved($deletingMemberOrganizationId, $deletingMemberUserId));

        $remainingUsers = $this->getEm()->getRepository(User::class)->findAll();
        $this->assertCount(7, $remainingUsers);
    }
}