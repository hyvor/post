<?php

namespace App\Tests\Api\Sudo\Newsletter;

use App\Api\Sudo\Controller\NewsletterController;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
class GetNewslettersTest extends WebTestCase
{
    public function test_get_newsletters(): void
    {
        NewsletterFactory::createMany(5);

        $response = $this->sudoApi(
            'GET',
            '/newsletters'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{newsletters: list<array<string, mixed>>, orgs: list<mixed>} $data */
        $data = $this->getJson();
        $this->assertArrayHasKey('newsletters', $data);
        $this->assertArrayHasKey('orgs', $data);
        $this->assertCount(5, $data['newsletters']);

        $newsletter = $data['newsletters'][0];
        $this->assertArrayHasKey('id', $newsletter);
        $this->assertArrayHasKey('created_at', $newsletter);
        $this->assertArrayHasKey('subdomain', $newsletter);
        $this->assertArrayHasKey('name', $newsletter);
        $this->assertArrayHasKey('user_id', $newsletter);
        $this->assertArrayHasKey('organization_id', $newsletter);
        $this->assertArrayHasKey('language_code', $newsletter);
        $this->assertArrayHasKey('is_rtl', $newsletter);
    }

    public function test_get_newsletters_by_name(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'name' => 'My Unique Newsletter',
        ]);
        NewsletterFactory::createMany(3);

        $response = $this->sudoApi(
            'GET',
            '/newsletters?name=My Unique Newsletter'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{newsletters: list<array<string, mixed>>} $data */
        $data = $this->getJson();
        $this->assertArrayHasKey('newsletters', $data);
        $this->assertCount(1, $data['newsletters']);

        $item = $data['newsletters'][0];
        $this->assertSame($newsletter->getId(), $item['id']);
    }

    public function test_get_newsletters_by_subdomain(): void
    {
        $newsletter = NewsletterFactory::createOne(['subdomain' => 'unique-sub-x42']);
        NewsletterFactory::createMany(3);

        $response = $this->sudoApi('GET', '/newsletters?name=unique-sub-x42');

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{newsletters: list<array<string, mixed>>} $data */
        $data = $this->getJson();
        $this->assertCount(1, $data['newsletters']);
        $this->assertSame($newsletter->getId(), $data['newsletters'][0]['id']);
    }

    public function test_sort_by_most_subscribers(): void
    {
        $few = NewsletterFactory::createOne();
        $many = NewsletterFactory::createOne();

        SubscriberFactory::createMany(2, ['newsletter' => $few]);
        SubscriberFactory::createMany(7, ['newsletter' => $many]);

        $response = $this->sudoApi('GET', '/newsletters?sort=most_subscribers');

        $this->assertSame(200, $response->getStatusCode());
        /** @var array{newsletters: list<array{id: int}>} $data */
        $data = $this->getJson();
        $this->assertSame($many->getId(), $data['newsletters'][0]['id']);
        $this->assertSame($few->getId(), $data['newsletters'][1]['id']);
    }
}
