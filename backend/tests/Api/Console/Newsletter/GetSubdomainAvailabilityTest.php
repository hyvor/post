<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
class GetSubdomainAvailabilityTest extends WebTestCase
{
    public function testAvailableSubdomain(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => 'unique-subdomain'
            ],
            useSession: true
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }

    public function test_reservedSubdomain(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => 'new'
            ],
            useSession: true
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertFalse($json['available']);
    }

    public function testUnavailableSubdomain(): void
    {
        NewsletterFactory::createOne([
            'subdomain' => 'taken-subdomain'
        ]);
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => 'taken-subdomain'
            ],
            useSession: true
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertFalse($json['available']);
    }

    public function testEmptySubdomain(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => ''
            ],
            useSession: true
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);;
        $this->assertStringContainsString('This value should not be blank.', $json['message']);
    }

    public function testTooLongSubdomain(): void
    {
        $longSubdomain = str_repeat('a', 51);
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => $longSubdomain
            ],
            useSession: true
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);;
        $this->assertStringContainsString('This value is too long. It should have 50 characters or less.', $json['message']);
    }

    public function testInvalidCharactersSubdomain(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter/subdomain',
            [
                'subdomain' => 'Invalid_Subdomain!'
            ],
            useSession: true
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);;
        $this->assertStringContainsString('Subdomain must start and end with a letter or digit.', $json['message']);
    }
}
