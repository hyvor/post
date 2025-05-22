<?php

namespace App\Tests\Api\Console\SubscriberMetadata;

use App\Api\Console\Controller\SubscriberMetadataController;
use App\Entity\SubscriberMetadataDefinition;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberMetadataController::class)]
#[CoversClass(SubscriberMetadataDefinition::class)]
#[CoversClass(SubscriberMetadataService::class)]
class DeleteSubscriberMetadataDefinitionTest extends WebTestCase
{

    public function test_cannot_delete_other_newsletter_entities(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $otherNewsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscriber-metadata-definitions/' . $metadata->getId()
        );

        $this->assertResponseStatusCodeSame(403);

        $json = $this->getJson();
        $this->assertSame('Entity does not belong to the newsletter', $json['message']);
    }

    public function test_deletes_metadata(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $metadataId = $metadata->getId();

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscriber-metadata-definitions/' . $metadataId
        );

        $this->assertResponseStatusCodeSame(200);

        $this->assertNull(
            $this->em->getRepository(SubscriberMetadataDefinition::class)->find($metadataId),
        );
    }

}