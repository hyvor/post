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
class UpdateSubscriberMetadataDefinitionTest extends WebTestCase
{

    public function test_updates_name(): void
    {
        $project = NewsletterFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscriber-metadata-definitions/' . $metadata->getId(),
            [
                'name' => 'Test Name 2',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson();
        $this->assertEquals('Test Name 2', $json['name']);

        $entity = $this->em->getRepository(SubscriberMetadataDefinition::class)->find($metadata->getId());
        $this->assertNotNull($entity);
        $this->assertEquals('Test Name 2', $entity->getName());
    }

    public function test_cannot_update_other_project_entities(): void
    {
        $project = NewsletterFactory::createOne();
        $otherProject = NewsletterFactory::createOne();

        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'project' => $otherProject,
        ]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscriber-metadata-definitions/' . $metadata->getId()
        );

        $this->assertResponseStatusCodeSame(403);
        $json = $this->getJson();
        $this->assertSame('Entity does not belong to the project', $json['message']);
    }


}