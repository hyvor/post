<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
#[CoversClass(SubscriberObject::class)]
class GetSubscribersTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testListSubscribersNonEmpty(): void
    {
        $project = ProjectFactory::createOne();

        $newsletterList1 = NewsletterListFactory::createOne(['project' => $project]);
        $newsletterList2 = NewsletterListFactory::createOne(['project' => $project]);

        $subscribers = SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$newsletterList1, $newsletterList2],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $projectOther = ProjectFactory::createOne();
        SubscriberFactory::createMany(2, [
            'project' => $projectOther,
            'lists' => [NewsletterListFactory::createOne(['project' => $project])],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertCount(5, $json);

        $subscriber = $json[4];
        $this->assertIsArray($subscriber);
        $this->assertArrayHasKey('id', $subscriber);
        $this->assertArrayHasKey('email', $subscriber);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriberDb = $repository->find($subscriber['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertSame($subscribers[0]->getEmail(), $subscriberDb->getEmail());
        $this->assertSame($subscribers[0]->getProject(), $subscriberDb->getProject());
        $this->assertSame($subscribers[0]->getLists(), $subscriberDb->getLists());
    }


    public function testListSubscribersPagination(): void
    {
        $project = ProjectFactory::createOne();
        SubscriberFactory::createMany(5, [
            'project' => $project,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers?limit=2&offset=1'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(2, $json);

        $subscriber = $json[1];
        $this->assertIsArray($subscriber);
        $this->assertArrayHasKey('id', $subscriber);
        $this->assertArrayHasKey('email', $subscriber);
    }

    public function testListSubscribersEmpty(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(0, $json);
    }

    #[TestWith([SubscriberStatus::SUBSCRIBED, SubscriberStatus::UNSUBSCRIBED])]
    #[TestWith([SubscriberStatus::UNSUBSCRIBED, SubscriberStatus::SUBSCRIBED])]
    public function testListSubscribersByStatus(SubscriberStatus $status, SubscriberStatus $oppositeStatus): void
    {
        $project = ProjectFactory::createOne();

        $newsletterList1 = NewsletterListFactory::createOne(['project' => $project]);

        $subscribers = SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$newsletterList1],
            'status' => $status,
        ]);

        // Opposite status subscribers
        SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$newsletterList1],
            'status' => $oppositeStatus,
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            "/subscribers?status={$status->value}"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(5, $json);

        $subscriber = $json[4];
        $this->assertIsArray($subscriber);
        $this->assertArrayHasKey('id', $subscriber);
        $this->assertArrayHasKey('email', $subscriber);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriberDb = $repository->find($subscriber['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriberDb);
        $this->assertSame($subscribers[0]->getEmail(), $subscriberDb->getEmail());
        $this->assertSame($subscribers[0]->getProject(), $subscriberDb->getProject());
        $this->assertSame($subscribers[0]->getLists(), $subscriberDb->getLists());
    }

    public function test_list_subscribers_email_search(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $subscriber1 = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'thibault@hyvor.com',
        ]);

        $subscriber2 = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'supun@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            "/subscribers?search=thibault"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $this->assertSame($subscriber1->getId(), $json[0]['id']);
    }


    public function test_list_subscribers_list_search(): void
    {
        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(
            [
                'project' => $project,
                'name' => 'list_1'
            ]
        );

        $list2 = NewsletterListFactory::createOne(
            [
                'project' => $project,
                'name' => 'list_2'
            ]
        );

        $subscriber1 = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list1],
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'thibault@hyvor.com',
        ]);

        $subscriber2 = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list2],
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'supun@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            "/subscribers?list_id={$list1->getId()}"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $this->assertSame($subscriber1->getId(), $json[0]['id']);
    }
}
