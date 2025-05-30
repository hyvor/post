<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Api\Console\Object\NewsletterListObject;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterListObject::class)]
class ConsoleInitNewsletterTest extends WebTestCase
{

    public function test_stats_subscribers_and_issues(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        // --- subscribers

        // subscribed this month
        SubscriberFactory::createMany(4, [
            'subscribed_at' => new \DateTimeImmutable('-20 days'),
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);
        // subscribed last month
        SubscriberFactory::createMany(3, [
            'subscribed_at' => new \DateTimeImmutable('-40 days'),
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);
        // unsubscribed
        SubscriberFactory::createMany(2, [
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);
        // other newsletter
        SubscriberFactory::createMany(3, [
            'newsletter' => $otherNewsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        // --- issues

        // sent this month
        IssueFactory::createMany(3, [
            'sent_at' => new \DateTimeImmutable('-10 days'),
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
        ]);
        // sent last month
        IssueFactory::createMany(2, [
            'sent_at' => new \DateTimeImmutable('-40 days'),
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
        ]);
        // other newsletter
        IssueFactory::createMany(2, [
            'newsletter' => $otherNewsletter,
            'status' => IssueStatus::SENT,
        ]);
        // draft issues
        IssueFactory::createMany(2, [
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
        ]);

        $response = $this->consoleApi(
            $newsletter->getId(),
            'GET',
            '/init/newsletter',
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);

        $stats = $data['stats'];
        $this->assertIsArray($stats['subscribers']);
        $this->assertIsArray($stats['issues']);
        $this->assertIsArray($stats['open_rate']);
        $this->assertIsArray($stats['click_rate']);

        $subscribers = $stats['subscribers'];
        $this->assertSame(7, $subscribers['total']);
        $this->assertSame(4, $subscribers['last_30_days']);

        $issues = $stats['issues'];
        $this->assertSame(5, $issues['total']);
        $this->assertSame(3, $issues['last_30_days']);
    }

    public function test_stats_open_click_rates(): void
    {

        $newsletter = NewsletterFactory::createOne();

        $issueThisMonth = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
            'sent_at' => new \DateTimeImmutable('-10 days'),
            'total_sends' => 10,
            'opened_sends' => 5,
            'clicked_sends' => 2,
        ]);

        $issueLastMonth = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'sent_at' => new \DateTimeImmutable('-40 days'),
            'status' => IssueStatus::SENT,
            'total_sends' => 20,
            'opened_sends' => 7,
            'clicked_sends' => 7,
        ]);

        $issueDraft = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/init/newsletter',
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $stats = $json['stats'];
        $this->assertIsArray($stats);

        $openRate = $stats['open_rate'];
        $this->assertIsArray($openRate);
        $this->assertSame(40, $openRate['total']);
        $this->assertSame(50, $openRate['last_30_days']);

        $clickRate = $stats['click_rate'];
        $this->assertIsArray($clickRate);
        $this->assertSame(30, $clickRate['total']);
        $this->assertSame(20, $clickRate['last_30_days']);

    }


}