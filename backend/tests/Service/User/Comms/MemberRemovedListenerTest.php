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

        $newsletters = NewsletterFactory::createMany(2, [
            'created_by_user_id' => $removingMemberUserId,
            'organization_id' => $removingMemberOrganizationId,
        ]);
        UserFactory::createOne([
            'newsletter_id' => $newsletters[0]->getId(),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        UserFactory::createOne([
            'newsletter_id' => $newsletters[1]->getId(),
            'hyvor_user_id' => $removingMemberUserId
        ]);

        $moreNewsletters1 = NewsletterFactory::createMany(3, [
            'created_by_user_id' => $removingMemberUserId,
            'organization_id' => 2,
        ]);
        UserFactory::createOne([
            'newsletter_Id' => $moreNewsletters1[0]->getId(),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        UserFactory::createOne([
            'newsletter_Id' => $moreNewsletters1[1]->getId(),
            'hyvor_user_id' => $removingMemberUserId
        ]);
        UserFactory::createOne([
            'newsletter_Id' => $moreNewsletters1[2]->getId(),
            'hyvor_user_id' => $removingMemberUserId
        ]);

        $moreNewsletters2 = NewsletterFactory::createMany(4, [
            'organization_id' => $removingMemberOrganizationId,
        ]);
        UserFactory::createOne([
            'newsletter_id' => $moreNewsletters2[0]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $moreNewsletters2[1]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $moreNewsletters2[2]->getId(),
        ]);
        UserFactory::createOne([
            'newsletter_id' => $moreNewsletters2[3]->getId(),
        ]);

        $this->getEd()->dispatch(new MemberRemoved($removingMemberOrganizationId, $removingMemberUserId));

        $remainingUsers = $this->getEm()->getRepository(User::class)->findAll();
        $this->assertCount(7, $remainingUsers);
    }
}