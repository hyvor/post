<?php

namespace App\Tests\Api\Console\ApiKey;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Controller\ApiKeyController;
use App\Api\Console\Input\ApiKey\CreateApiKeyInput;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
#[CoversClass(ApiKeyService::class)]
#[CoversClass(Scope::class)]
#[CoversClass(CreateApiKeyInput::class)]
#[CoversClass(ApiKeyObject::class)]
class CreateApiKeyTest extends WebTestCase
{
    public function test_create_api_key(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys',
            [
                'name' => 'Test name',
                'scopes' => [Scope::ISSUE_READ, Scope::ISSUE_WRITE]
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $this->getJson();

        $this->assertArrayHasKey('key', $content);
        $this->assertNotNull($content['key']);
        $this->assertArrayHasKey('created_at', $content);

        $apiKey = $this->em->getRepository(ApiKey::class)->findOneBy([
            'id' => $content['id'],
        ]);
        $this->assertNotNull($apiKey);
        $this->assertSame('Test name', $apiKey->getName());
        $this->assertSame([Scope::ISSUE_READ, Scope::ISSUE_WRITE], $apiKey->getScopes());
    }

    public function test_create_api_key_without_name(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys',
            [
                'scopes' => [Scope::ISSUE_READ, Scope::ISSUE_WRITE]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());

        $this->assertHasViolation('name', 'This value should not be blank.');
    }

    public function test_create_api_key_without_scope(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys',
            [
                'name' => 'Test name'
            ]
        );

        $this->assertSame(422, $response->getStatusCode());

        $this->assertHasViolation('scopes', 'This value should not be blank.');
    }

    public function test_create_api_key_invalid_scope(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys',
            [
                'name' => 'Test name',
                'scopes' => [Scope::ISSUE_READ, Scope::ISSUE_WRITE, 'invalid_scope']
            ]
        );
        $this->assertSame(422, $response->getStatusCode());
        $this->assertHasViolation('scopes[2]', 'The value you selected is not a valid choice.');
    }

    public function test_create_api_key_reaching_limit(): void
    {
        $newsletter = NewsletterFactory::createOne();

        ApiKeyFactory::createMany(10, [
            'newsletter' => $newsletter,
            'is_enabled' => true,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys',
            [
                'name' => 'Exceeding limit',
                'scopes' => [Scope::ISSUE_READ, Scope::ISSUE_WRITE]
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $content = $this->getJson();
        $this->assertArrayHasKey('message', $content);
        $this->assertSame('You have reached the maximum number of API keys for this newsletter.', $content['message']);
    }
}
