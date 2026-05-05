<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
#[CoversClass(SubscriberObject::class)]
class GetSubscriberByEmailTest extends WebTestCase
{

    public function test_get_subscriber_by_email_found(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'list-one']);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'list-two']);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'email' => 'test@example.com',
            'lists' => [$list1, $list2],
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/subscribers/email/test@example.com',
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<string, mixed> $json */
        $json = $this->getJson();

        $this->assertSame($subscriber->getId(), $json['id']);
        $this->assertSame('test@example.com', $json['email']);
        $this->assertContains($list1->getId(), $json['list_ids']);
        $this->assertContains($list2->getId(), $json['list_ids']);
        $this->assertContains('list-one', $json['lists']);
        $this->assertContains('list-two', $json['lists']);
    }

    public function test_get_subscriber_by_email_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/subscribers/email/nonexistent@example.com',
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_get_subscriber_by_email_other_newsletter(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'email' => 'test@example.com',
        ]);

        $response = $this->consoleApi(
            $otherNewsletter,
            'GET',
            '/subscribers/email/test@example.com',
        );

        $this->assertSame(404, $response->getStatusCode());
    }

}
