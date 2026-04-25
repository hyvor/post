<?php

namespace App\Tests\Api\Sudo\Newsletter;

use App\Api\Sudo\Controller\NewsletterController;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
class GetNewslettersStatsTest extends WebTestCase
{
    public function test_returns_counts_per_newsletter(): void
    {
        $a = NewsletterFactory::createOne();
        $b = NewsletterFactory::createOne();

        IssueFactory::createMany(3, ['newsletter' => $a]);
        IssueFactory::createMany(1, ['newsletter' => $b]);
        SubscriberFactory::createMany(5, ['newsletter' => $a]);
        SubscriberFactory::createMany(2, ['newsletter' => $b]);

        $response = $this->sudoApi(
            'GET',
            '/newsletters/stats?ids=' . $a->getId() . ',' . $b->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{stats: array<int, array{issues_count: int, subscribers_count: int}>} $data */
        $data = $this->getJson();
        $this->assertSame(3, $data['stats'][$a->getId()]['issues_count']);
        $this->assertSame(5, $data['stats'][$a->getId()]['subscribers_count']);
        $this->assertSame(1, $data['stats'][$b->getId()]['issues_count']);
        $this->assertSame(2, $data['stats'][$b->getId()]['subscribers_count']);
    }

    public function test_zero_counts_for_newsletter_with_no_data(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->sudoApi(
            'GET',
            '/newsletters/stats?ids=' . $newsletter->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{stats: array<int, array{issues_count: int, subscribers_count: int}>} $data */
        $data = $this->getJson();
        $this->assertSame(0, $data['stats'][$newsletter->getId()]['issues_count']);
        $this->assertSame(0, $data['stats'][$newsletter->getId()]['subscribers_count']);
    }
}
