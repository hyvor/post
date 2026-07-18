<?php

namespace App\Tests\Api\Sudo\Newsletter;

use App\Api\Sudo\Controller\NewsletterController;
use App\Api\Sudo\Input\Newsletter\UpdateNewsletterInput;
use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(UpdateNewsletterInput::class)]
#[CoversClass(Newsletter::class)]
class UpdateNewsletterTest extends WebTestCase
{
    public function test_updates_daily_sending_rate(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'daily_sending_rate' => null,
        ]);

        $response = $this->sudoApi(
            'PATCH',
            '/newsletters/' . $newsletter->getId(),
            [
                'daily_sending_rate' => 1000,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $data = $this->getJson();
        $this->assertArrayHasKey('newsletter', $data);
        $this->assertIsArray($data['newsletter']);
        $this->assertSame(1000, $data['newsletter']['daily_sending_rate']);

        $this->assertSame(1000, $newsletter->getDailySendingRate());
    }

    public function test_resets_daily_sending_rate_to_default(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'daily_sending_rate' => 1000,
        ]);

        $response = $this->sudoApi(
            'PATCH',
            '/newsletters/' . $newsletter->getId(),
            [
                'daily_sending_rate' => null,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $data = $this->getJson();
        $this->assertArrayHasKey('newsletter', $data);
        $this->assertIsArray($data['newsletter']);
        $this->assertNull($data['newsletter']['daily_sending_rate']);
    }

    public function test_rejects_non_positive_rate(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'daily_sending_rate' => 500,
        ]);

        $response = $this->sudoApi(
            'PATCH',
            '/newsletters/' . $newsletter->getId(),
            [
                'daily_sending_rate' => 0,
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_not_found(): void
    {
        $response = $this->sudoApi(
            'PATCH',
            '/newsletters/99999',
            [
                'daily_sending_rate' => 1000,
            ]
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
