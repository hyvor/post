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
        $removingMemberUserId = 12345;
        $removingMemberOrganizationId = 1;

        UserFactory::createMany(2, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => $removingMemberOrganizationId
            ]),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        UserFactory::createMany(3, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => 2
            ]),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        UserFactory::createMany(4, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => $removingMemberOrganizationId
            ])
        ]);

        $this->getEd()->dispatch(new MemberRemoved($removingMemberOrganizationId, $removingMemberUserId));

        $remainingUsers = $this->getEm()->getRepository(User::class)->findAll();
        $this->assertCount(7, $remainingUsers);
    }
}