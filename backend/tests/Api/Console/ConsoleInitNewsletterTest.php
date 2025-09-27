<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Api\Console\Object\NewsletterListObject;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\PostLicense;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterListObject::class)]
class ConsoleInitNewsletterTest extends WebTestCase
{

    public function test_stats_subscribers_and_issues(): void
    {
        BillingFake::enableForSymfony(
            $this->container,
            license: new PostLicense(allowRemoveBranding: true)
        );

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
        // other newsletters
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
        $this->assertIsArray($stats['bounced_rate']);
        $this->assertIsArray($stats['complained_rate']);

        $subscribers = $stats['subscribers'];
        $this->assertSame(7, $subscribers['total']);
        $this->assertSame(4, $subscribers['last_30_days']);

        $issues = $stats['issues'];
        $this->assertSame(5, $issues['total']);
        $this->assertSame(3, $issues['last_30_days']);

        $permissions = $data['permissions'];
        $this->assertIsArray($permissions);
        $this->assertTrue($permissions['can_change_branding']);
    }

    public function test_when_can_no_permissions_to_change_branding(): void
    {
        BillingFake::enableForSymfony(
            $this->container,
            license: new PostLicense(allowRemoveBranding: false)
        );

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter->getId(),
            'GET',
            '/init/newsletter',
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson();

        $permissions = $data['permissions'];
        $this->assertIsArray($permissions);
        $this->assertFalse($permissions['can_change_branding']);
    }

    public function test_stats_bounced_complained_rates(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issueThisMonth = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
            'sent_at' => new \DateTimeImmutable('-10 days'),
        ]);

        $issueLastMonth = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'sent_at' => new \DateTimeImmutable('-40 days'),
            'status' => IssueStatus::SENT,
        ]);

        $sendDateThisMonth = new \DateTimeImmutable('-10 days');
        // 8 sends for this month issue: 2 bounced, 1 complained
        SendFactory::createMany(2, [
            'newsletter' => $newsletter,
            'issue' => $issueThisMonth,
            'sent_at' => $sendDateThisMonth,
            'status' => SendStatus::SENT,
            'bounced_at' => $sendDateThisMonth,
        ]);
        SendFactory::createOne([
            'newsletter' => $newsletter,
            'issue' => $issueThisMonth,
            'sent_at' => $sendDateThisMonth,
            'status' => SendStatus::SENT,
            'complained_at' => $sendDateThisMonth,
        ]);
        SendFactory::createMany(5, [
            'newsletter' => $newsletter,
            'issue' => $issueThisMonth,
            'sent_at' => $sendDateThisMonth,
            'status' => SendStatus::SENT,
        ]);

        $sendDateLastMonth = new \DateTimeImmutable('-40 days');
        // 14 sends for last month issue: 4 bounced, 2 complained
        SendFactory::createMany(4, [
            'newsletter' => $newsletter,
            'issue' => $issueLastMonth,
            'sent_at' => $sendDateLastMonth,
            'status' => SendStatus::SENT,
            'bounced_at' => $sendDateLastMonth,
        ]);
        SendFactory::createMany(2, [
            'newsletter' => $newsletter,
            'issue' => $issueLastMonth,
            'sent_at' => $sendDateLastMonth,
            'status' => SendStatus::SENT,
            'complained_at' => $sendDateLastMonth,
        ]);
        SendFactory::createMany(8, [
            'newsletter' => $newsletter,
            'issue' => $issueLastMonth,
            'sent_at' => $sendDateLastMonth,
            'status' => SendStatus::SENT,
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

        $bouncedRate = $stats['bounced_rate'];
        $this->assertIsArray($bouncedRate);
        $this->assertSame(27.27, $bouncedRate['total']);
        $this->assertSame(25, $bouncedRate['last_30_days']);

        $complainedRate = $stats['complained_rate'];
        $this->assertIsArray($complainedRate);
        $this->assertSame(13.64, $complainedRate['total']);
        $this->assertSame(12.5, $complainedRate['last_30_days']);
    }


}
