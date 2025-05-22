<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\SendObject;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
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

        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );

        $send = SendFactory::createOne(
            [
                'issue' => $issue,
                'subscriber' => $subscriber,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/sends"
        );
        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();

        $this->assertSame(1, count($json));
        $this->assertSame($send->getId(), $json[0]['id']);
        $this->assertSame($send->getCreatedAt()->getTimestamp(), $json[0]['created_at']);
    }

    public function test_get_sends_limit(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );

        SendFactory::createMany(
            10,
            [
                'issue' => $issue,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/sends?limit=5"
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame(5, count($json));
    }

    public function test_get_sends_email_search(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );

        $send1 = SendFactory::createOne(
            [
                'issue' => $issue,
                'email' => 'thibault@hyvor.com'
            ]
        );

        $send2 = SendFactory::createOne(
            [
                'issue' => $issue,
                'email' => 'supun@hyvor.com'
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/sends?search=thibault"
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();

        $this->assertSame(1, count($json));
        $this->assertSame($send1->getId(), $json[0]['id']);
    }

    public function test_get_sends_clicked_search(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );

        $send1 = SendFactory::createOne(
            [
                'issue' => $issue,
                'email' => 'thibault@hyvor.com',
                'first_clicked_at' => new \DateTimeImmutable()
            ]
        );

        $send2 = SendFactory::createOne(
            [
                'issue' => $issue,
                'email' => 'supun@hyvor.com'
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/sends?type=clicked"
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();
        $this->assertSame(1, count($json));
        $this->assertSame($send1->getId(), $json[0]['id']);
    }
}
