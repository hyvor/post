<?php

namespace App\Tests\MessageHandler\Subscriber;

use App\Entity\Subscriber;
use App\Service\Subscriber\Message\UnsubscribeByEmailMessage;
use App\Service\Subscriber\MessageHandler\UnsubscribeByEmailMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnsubscribeByEmailMessageHandler::class)]
class UnsubscribeByEmailMessageHandlerTest extends KernelTestCase
{
    public function test_clears_lists_and_sets_reason_for_matching_email(): void
    {
        $list1 = NewsletterListFactory::createOne();
        $list2 = NewsletterListFactory::createOne();

        $s1 = SubscriberFactory::createOne([
            'email' => 'suppressed@example.com',
            'lists' => [$list1, $list2],
        ]);
        $s2 = SubscriberFactory::createOne([
            'email' => 'suppressed@example.com',
            'lists' => [$list1],
        ]);
        $other = SubscriberFactory::createOne([
            'email' => 'other@example.com',
            'lists' => [$list2],
        ]);

        $transport = $this->transport('async');
        $transport->send(new UnsubscribeByEmailMessage('suppressed@example.com', 'bounce - Hard bounce'));
        $transport->throwExceptions()->process();

        $repo = $this->em->getRepository(Subscriber::class);

        $updated1 = $repo->find($s1->getId());
        $this->assertNotNull($updated1);
        $this->assertCount(0, $updated1->getLists());
        $this->assertSame('bounce - Hard bounce', $updated1->getUnsubscribeReason());

        $updated2 = $repo->find($s2->getId());
        $this->assertNotNull($updated2);
        $this->assertCount(0, $updated2->getLists());
        $this->assertSame('bounce - Hard bounce', $updated2->getUnsubscribeReason());

        $untouched = $repo->find($other->getId());
        $this->assertNotNull($untouched);
        $this->assertCount(1, $untouched->getLists());
        $this->assertNull($untouched->getUnsubscribeReason());
    }

    public function test_processes_all_subscribers_across_chunks(): void
    {
        // 150 > CHUNK_SIZE (100), so the handler must paginate
        SubscriberFactory::createMany(150, ['email' => 'bulk@example.com']);

        $transport = $this->transport('async');
        $transport->send(new UnsubscribeByEmailMessage('bulk@example.com', 'complaint'));
        $transport->throwExceptions()->process();

        $all = $this->em->getRepository(Subscriber::class)->findBy(['email' => 'bulk@example.com']);
        $this->assertCount(150, $all);

        foreach ($all as $subscriber) {
            $this->assertSame('complaint', $subscriber->getUnsubscribeReason());
        }
    }
}
