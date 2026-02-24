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
class GetNewsletterTest extends WebTestCase
{
    public function test_get_newsletter(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->sudoApi(
            'GET',
            '/newsletters/' . $newsletter->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('newsletter', $data);
        $this->assertArrayHasKey('stats', $data);
    }

    public function test_get_newsletter_not_found(): void
    {
        $response = $this->sudoApi(
            'GET',
            '/newsletters/99999'
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
