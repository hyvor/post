<?php

namespace App\Tests\Api\Console\SubscriberMetadata;

use App\Api\Console\Controller\SubscriberMetadataController;
use App\Entity\SubscriberMetadataDefinition;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberMetadataController::class)]
#[CoversClass(SubscriberMetadataDefinition::class)]
#[CoversClass(SubscriberMetadataService::class)]
class DeleteSubscriberMetadataDefinitionTest extends WebTestCase
{

    public function test_cannot_delete_other_project_entities(): void
    {
        $project = ProjectFactory::createOne();
        $otherProject = ProjectFactory::createOne();

        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'project' => $otherProject,
        ]);

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/subscriber-metadata-definitions/' . $metadata->getId()
        );

        $this->assertResponseStatusCodeSame(403);

        $json = $this->getJson();
        $this->assertSame('Entity does not belong to the project', $json['message']);
    }

    public function test_deletes_metadata(): void
    {
        $project = ProjectFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'project' => $project,
        ]);

        $metadataId = $metadata->getId();

        $this->consoleApi(
            $project,
            'DELETE',
            '/subscriber-metadata-definitions/' . $metadataId
        );

        $this->assertResponseStatusCodeSame(200);

        $this->assertNull(
            $this->em->getRepository(SubscriberMetadataDefinition::class)->find($metadataId),
        );
    }

}