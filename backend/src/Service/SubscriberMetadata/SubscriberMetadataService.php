<?php

namespace App\Service\SubscriberMetadata;

use App\Entity\Newsletter;
use App\Entity\SubscriberMetadataDefinition;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SubscriberMetadataService
{

    public const int MAX_METADATA_DEFINITIONS_PER_NEWSLETTER = 20;

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @return SubscriberMetadataDefinition[]
     */
    public function getMetadataDefinitions(Newsletter $newsletter): array
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findBy(['newsletter' => $newsletter]);
    }

    public function getMetadataDefinitionByKey(Newsletter $newsletter, string $key): ?SubscriberMetadataDefinition
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findOneBy(['newsletter' => $newsletter, 'key' => $key]);
    }

    public function getMetadataDefinitionsCount(Newsletter $newsletter): int
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->count(['newsletter' => $newsletter]);
    }

    public function createMetadataDefinition(
        Newsletter $newsletter,
        string     $key,
        string     $name,
    ): SubscriberMetadataDefinition
    {
        $metadataDefinition = new SubscriberMetadataDefinition();
        $metadataDefinition->setNewsletter($newsletter);
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
        string                       $name,
    ): void
    {
        $metadataDefinition->setName($name);
        $metadataDefinition->setUpdatedAt($this->now());

        $this->entityManager->flush();
    }

    public function deleteMetadataDefinition(SubscriberMetadataDefinition $metadataDefinition): void
    {
        $this->entityManager->remove($metadataDefinition);
        $this->entityManager->flush();
    }

    public function validateValueType(
        SubscriberMetadataDefinition $metadataDefinition,
        mixed                        $value
    ): bool
    {
        return match ($metadataDefinition->getType()) {
            //  @phpstan-ignore-next-line
            SubscriberMetadataDefinitionType::TEXT => is_string($value),
            // Other Metadata types can be added here
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function validateMetadata(Newsletter $newsletter, array $metadata): bool
    {
        foreach ($metadata as $key => $value) {
            $metaDef = $this->getMetadataDefinitionByKey($newsletter, $key);
            if ($metaDef === null)
                throw new \Exception("Metadata definition with key {$key} not found");
            if (!$this->validateValueType($metaDef, $value)) {
                throw new \Exception("Value for metadata key {$key} is not valid");
            }
        }
        return true;
    }
}
