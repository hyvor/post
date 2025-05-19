<?php

namespace App\Tests\Api\Console\SubscriberMetadata;

use App\Entity\SubscriberMetadataDefinition;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;

class CreateSubscriberMetadataDefinitionTest extends WebTestCase
{

    public function test_key_cannot_be_blank(): void
    {
        $project = ProjectFactory::createOne();
        $response = $this->consoleApi(
            $project,
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
        $project = ProjectFactory::createOne();
        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test.key.1',
                'name' => 'Test Name',
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('key', 'Key can only contain letters, numbers, underscores and dashes');
    }

    public function test_cannot_create_when_key_exists(): void
    {
        $project = ProjectFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createOne(
            [
                'project' => $project,
                'key' => 'test-key',
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test-key',
                'name' => 'Test Name 2',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson();
        $this->assertSame('Key already exists', $json['message']);
    }

    public function test_creates_subscriber_metadata_definition(): void
    {
        $project = ProjectFactory::createOne();
        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscriber-metadata-definitions',
            [
                'key' => 'test-key',
                'name' => 'Test Name',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson();
        $this->assertSame('test-key', $json['key']);
        $this->assertSame('Test Name', $json['name']);

        $entity = $this->em->getRepository(SubscriberMetadataDefinition::class)->find($json['id']);
        $this->assertNotNull($entity);
        $this->assertSame('test-key', $entity->getKey());
        $this->assertSame('Test Name', $entity->getName());
        $this->assertSame($project->getId(), $entity->getProject()->getId());
    }

}