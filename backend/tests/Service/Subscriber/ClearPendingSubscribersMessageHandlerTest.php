<?php

namespace App\Tests\Service\Subscriber;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Message\ClearPendingSubscribersMessage;
use App\Service\Subscriber\MessageHandler\ClearPendingSubscribersMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClearPendingSubscribersMessageHandler::class)]
class ClearPendingSubscribersMessageHandlerTest extends KernelTestCase
{
    public function test_clear_old_pending_subscribers(): void
    {
        $s1 = SubscriberFactory::createOne([
            'created_at' => new \DateTimeImmutable('-50 hours'),
            'status' => SubscriberStatus::PENDING
        ]);

        $s2 = SubscriberFactory::createOne([
            'created_at' => new \DateTimeImmutable('-10 hours'),
            'status' => SubscriberStatus::PENDING
        ]);

        $s3 = SubscriberFactory::createOne([
            'created_at' => new \DateTimeImmutable('-50 hours'),
            'status' => SubscriberStatus::SUBSCRIBED
        ]);

        $s4 = SubscriberFactory::createOne([
            'created_at' => new \DateTimeImmutable('-10 hours'),
            'status' => SubscriberStatus::SUBSCRIBED
        ]);

        $transport = $this->transport('scheduler');
        $transport->send(new ClearPendingSubscribersMessage());
        $transport->throwExceptions()->process();

        $repository = $this->em->getRepository(Subscriber::class);
        $this->assertNull($repository->find($s1->getId()));
        $this->assertNotNull($repository->find($s2->getId()));
        $this->assertNotNull($repository->find($s3->getId()));
        $this->assertNotNull($repository->find($s4->getId()));
    }
}
