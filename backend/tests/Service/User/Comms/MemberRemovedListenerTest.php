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

        $removingUsers = UserFactory::createMany(2, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => $removingMemberOrganizationId
            ]),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        $removingUserId = $removingUsers[0]->getId();

        $nonRemovingUsers1 = UserFactory::createMany(3, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => 2
            ]),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        $nonRemovingUserId1 = $nonRemovingUsers1[0]->getId();

        $nonRemovingUsers2 = UserFactory::createMany(4, [
            'newsletter' => NewsletterFactory::new([
                'organization_id' => $removingMemberOrganizationId
            ])
        ]);
        $nonRemovingUserId2 = $nonRemovingUsers2[0]->getId();

        $this->getEd()->dispatch(new MemberRemoved($removingMemberOrganizationId, $removingMemberUserId));

        $userRepository = $this->getEm()->getRepository(User::class);

        $remainingUsers = $userRepository->findAll();
        $this->assertCount(7, $remainingUsers);

        $this->assertNull($userRepository->findOneBy(['id' => $removingUserId]));
        $this->assertNotNull($userRepository->findOneBy(['id' => $nonRemovingUserId1]));
        $this->assertNotNull($userRepository->findOneBy(['id' => $nonRemovingUserId2]));
    }
}