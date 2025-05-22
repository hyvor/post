<?php

namespace App\Tests\Api\Console\SubscriberMetadata;

use App\Api\Console\Controller\SubscriberMetadataController;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\SubscriberMetadataDefinition;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberMetadataController::class)]
#[CoversClass(SubscriberMetadataDefinition::class)]
#[CoversClass(SubscriberMetadataService::class)]
#[CoversClass(SubscriberMetadataDefinitionObject::class)]
class CreateSubscriberMetadataDefinitionTest extends WebTestCase
{

    public function test_key_cannot_be_blank(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => '',
                'name' => 'Test Name',
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('key', 'This value should not be blank.');
    }

    public function test_key_should_match_regex(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test.key.1',
                'name' => 'Test Name',
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('key', 'Key can only contain lowercase letters, numbers, and underscores');
    }

    public function test_cannot_create_when_key_exists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createOne(
            [
                'newsletter' => $newsletter,
                'key' => 'test_key',
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test_key',
                'name' => 'Test Name 2',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson();
        $this->assertSame('Key already exists', $json['message']);
    }

    public function test_creates_subscriber_metadata_definition(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test_key',
                'name' => 'Test Name',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson();
        $this->assertSame('test_key', $json['key']);
        $this->assertSame('Test Name', $json['name']);

        $entity = $this->em->getRepository(SubscriberMetadataDefinition::class)->find($json['id']);
        $this->assertNotNull($entity);
        $this->assertSame('test_key', $entity->getKey());
        $this->assertSame('Test Name', $entity->getName());
        $this->assertSame($newsletter->getId(), $entity->getNewsletter()->getId());
    }

    public function test_cannot_create_after_the_limit(): void
    {
        $limit = SubscriberMetadataService::MAX_METADATA_DEFINITIONS_PER_NEWSLETTER;

        $newsletter = NewsletterFactory::createOne();
        SubscriberMetadataDefinitionFactory::createMany($limit, [
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test_key',
                'name' => 'Test Name 2',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson();
        $this->assertSame('Maximum number of metadata definitions reached', $json['message']);
    }

}