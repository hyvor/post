<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Subscriber;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberListUnsubscribedFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(NewsletterListService::class)]
class AddSubscriberListTest extends WebTestCase
{
    public function testAddSubscriberToListById(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(200, $response->getStatusCode());

        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(1, $subscriberDb->getLists());
        $this->assertSame($list->getId(), $subscriberDb->getLists()->first()->getId());
    }

    public function testAddSubscriberToListByName(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'My List']);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['name' => 'My List']
        );

        $this->assertSame(200, $response->getStatusCode());

        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(1, $subscriberDb->getLists());
        $this->assertSame($list->getId(), $subscriberDb->getLists()->first()->getId());
    }

    public function testAddSubscriberToListValidation(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            []
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Either id or name must be provided', $this->getJson()['message']);
    }

    public function testAddSubscriberToListNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => 999999]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('List not found', $this->getJson()['message']);
    }

    public function testAddSubscriberToListAlreadyInList(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(200, $response->getStatusCode());

        // Still only in the list once (idempotent)
        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(1, $subscriberDb->getLists());
    }

    public function testAddSubscriberToListIfUnsubscribedError(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListUnsubscribedFactory::createOne([
            'list' => $list,
            'subscriber' => $subscriber,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId(), 'if_unsubscribed' => 'error']
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            'Subscriber has previously unsubscribed from this list',
            $this->getJson()['message']
        );
    }

    public function testAddSubscriberToListIfUnsubscribedForceCreate(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListUnsubscribedFactory::createOne([
            'list' => $list,
            'subscriber' => $subscriber,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId(), 'if_unsubscribed' => 'force_create']
        );

        $this->assertSame(200, $response->getStatusCode());

        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(1, $subscriberDb->getLists());
    }

    public function testCannotAddSubscriberOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter1]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter2,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);
    }

    public function testCannotAddListOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();
        $listOfNewsletter2 = NewsletterListFactory::createOne(['newsletter' => $newsletter2]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter1,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $listOfNewsletter2->getId()]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('List not found', $this->getJson()['message']);
    }
}
