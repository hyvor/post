<?php

namespace App\Tests\Api\Sudo\Issue;

use App\Api\Console\Object\SendObject;
use App\Api\Sudo\Controller\IssueController;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(SendService::class)]
#[CoversClass(SendObject::class)]
class GetSendsTest extends WebTestCase
{
    public function test_get_sends_from_issue(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list]
        ]);

        $issue = IssueFactory::createOne(['newsletter' => $newsletter]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
        ]);

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId() . '/sends'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();

        $this->assertCount(1, $json);
        $this->assertSame($send->getId(), $json[0]['id']);
        $this->assertSame($send->getCreatedAt()->getTimestamp(), $json[0]['created_at']);
        $this->assertArrayHasKey('email', $json[0]);
        $this->assertArrayHasKey('status', $json[0]);
        $this->assertArrayHasKey('hard_bounce', $json[0]);
    }

    public function test_get_sends_limit(): void
    {
        $issue = IssueFactory::createOne();

        SendFactory::createMany(10, ['issue' => $issue]);

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId() . '/sends?limit=5'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(5, $this->getJson());
    }

    public function test_get_sends_email_search(): void
    {
        $issue = IssueFactory::createOne();

        $send1 = SendFactory::createOne([
            'issue' => $issue,
            'email' => 'thibault@hyvor.com',
        ]);
        SendFactory::createOne([
            'issue' => $issue,
            'email' => 'supun@hyvor.com',
        ]);

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId() . '/sends?search=thibault'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();

        $this->assertCount(1, $json);
        $this->assertSame($send1->getId(), $json[0]['id']);
    }

    public function test_get_sends_filters_by_type_bounced(): void
    {
        $issue = IssueFactory::createOne();

        $bounced = SendFactory::createOne([
            'issue' => $issue,
            'email' => 'thibault@hyvor.com',
            'bounced_at' => new \DateTimeImmutable(),
        ]);
        SendFactory::createOne([
            'issue' => $issue,
            'email' => 'supun@hyvor.com',
        ]);

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId() . '/sends?type=bounced'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();

        $this->assertCount(1, $json);
        $this->assertSame($bounced->getId(), $json[0]['id']);
    }

    public function test_get_sends_not_found(): void
    {
        $response = $this->sudoApi(
            'GET',
            '/issues/99999/sends'
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
