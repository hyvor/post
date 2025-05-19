<?php

namespace App\Service\SubscriberMetadata;

use App\Entity\Project;
use App\Entity\SubscriberMetadataDefinition;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SubscriberMetadataService
{

    public const int MAX_METADATA_DEFINITIONS_PER_PROJECT = 20;

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return SubscriberMetadataDefinition[]
     */
    public function getMetadataDefinitions(Project $project): array
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findBy(['project' => $project]);
    }

    public function getMetadataDefinitionByKey(Project $project, string $key): ?SubscriberMetadataDefinition
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findOneBy(['project' => $project, 'key' => $key]);
    }

    public function getMetadataDefinitionsCount(Project $project): int
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->count(['project' => $project]);
    }

    public function createMetadataDefinition(
        Project $project,
        string $key,
        string $name,
    ): SubscriberMetadataDefinition {
        $metadataDefinition = new SubscriberMetadataDefinition();
        $metadataDefinition->setProject($project);
        $metadataDefinition->setKey($key);
        $metadataDefinition->setName($name);
        $metadataDefinition->setType(SubscriberMetadataDefinitionType::TEXT);
        $metadataDefinition->setCreatedAt($this->now());
        $metadataDefinition->setUpdatedAt($this->now());

        $this->entityManager->persist($metadataDefinition);
        $this->entityManager->flush();

        return $metadataDefinition;
    }

    public function updateMetadataDefinition(
        SubscriberMetadataDefinition $metadataDefinition,
        string $name,
    ): void {
        $metadataDefinition->setName($name);
        $metadataDefinition->setUpdatedAt($this->now());

        $this->entityManager->flush();
    }

    public function deleteMetadataDefinition(SubscriberMetadataDefinition $metadataDefinition): void
    {
        $this->entityManager->remove($metadataDefinition);
        $this->entityManager->flush();
    }

}