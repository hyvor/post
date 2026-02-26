<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Subscriber;
use App\Entity\SubscriberListUnsubscribed;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(NewsletterListService::class)]
class RemoveSubscriberListTest extends WebTestCase
{
    public function testRemoveSubscriberFromListById(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(0, $subscriberDb->getLists());
    }

    public function testRemoveSubscriberFromListByName(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'Remove Me']);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['name' => 'Remove Me']
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $subscriberDb = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertCount(0, $subscriberDb->getLists());
    }

    public function testRemoveSubscriberFromListValidation(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            []
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Either id or name must be provided', $this->getJson()['message']);
    }

    public function testRemoveSubscriberFromListNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => 999999]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('List not found', $this->getJson()['message']);
    }

    public function testRemoveSubscriberFromListNotInList(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRemoveSubscriberFromListWithReasonUnsubscribe(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId(), 'reason' => 'unsubscribe']
        );

        $this->assertSame(200, $response->getStatusCode());

        $record = $this->em->getRepository(SubscriberListUnsubscribed::class)->findOneBy([
            'list' => $list->_real(),
            'subscriber' => $subscriber->_real(),
        ]);

        $this->assertInstanceOf(SubscriberListUnsubscribed::class, $record);
    }

    public function testRemoveSubscriberFromListWithReasonOther(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId(), 'reason' => 'other']
        );

        $this->assertSame(200, $response->getStatusCode());

        $record = $this->em->getRepository(SubscriberListUnsubscribed::class)->findOneBy([
            'list' => $list->_real(),
            'subscriber' => $subscriber->_real(),
        ]);

        $this->assertNull($record);
    }

    public function testCannotRemoveSubscriberOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter1]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter1, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter2,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $list->getId()]
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);
    }

    public function testCannotRemoveListOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();
        $listOfNewsletter2 = NewsletterListFactory::createOne(['newsletter' => $newsletter2]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter1,
            'DELETE',
            '/subscribers/' . $subscriber->getId() . '/lists',
            ['id' => $listOfNewsletter2->getId()]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('List not found', $this->getJson()['message']);
    }
}
