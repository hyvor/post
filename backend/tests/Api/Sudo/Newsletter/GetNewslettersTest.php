<?php

namespace App\Tests\Api\Sudo\Newsletter;

use App\Api\Sudo\Controller\NewsletterController;
use App\Api\Sudo\Object\NewsletterObject;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterObject::class)]
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
        $data = $this->getJson();
        $this->assertIsArray($data);
        $this->assertCount(5, $data);

        $newsletter = $data[0];
        $this->assertIsArray($newsletter);
        $this->assertCount(8, $newsletter);
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
        $data = $this->getJson();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $item = $data[0];
        $this->assertIsArray($item);
        $this->assertSame($newsletter->getId(), $item['id']);
    }
}
