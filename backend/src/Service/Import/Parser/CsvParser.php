<?php

namespace App\Service\Import\Parser;

use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Media\MediaReadException;
use App\Service\Media\MediaService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CsvParser extends ParserAbstract
{
    public function __construct(
        private MediaService $mediaService,
        private SubscriberMetadataService $subscriberMetadataService,
    )
    {
        parent::__construct();
    }

    /**
     * @return Collection<int, ImportingSubscriberDto>
     * @throws ParserException
     */
    public function parse(SubscriberImport $subscriberImport): Collection
    {
        $fieldMapping = $subscriberImport->getFields();

        if ($fieldMapping === null) {
            throw new ParserException('Field mapping not set.');
        }

        $metaFields = $this->getMetadataFields($fieldMapping);
        $metaKeys = array_map(fn($meta) => $meta->getKey(), $this->subscriberMetadataService->getMetadataDefinitions($subscriberImport->getNewsletter()));


        try {
            $stream = $this->mediaService->getMediaStream($subscriberImport->getMedia());
        } catch (MediaReadException $e) {
            throw new ParserException('Failed to read media stream: ' . $e->getMessage(), previous: $e);
        }

        if (!is_resource($stream)) {
            throw new ParserException('Unable to read media stream.');
        }

        $headers = fgetcsv($stream);
        if ($headers === false) {
            fclose($stream);
            throw new ParserException('CSV header row missing or invalid.');
        }
        $headers = array_filter($headers, fn($h) => $h !== null && $h !== '');

        $subscribers = [];
        $rowIndex = 1;

        while (($row = fgetcsv($stream)) !== false) {
            $rowIndex++;

            if (count($row) !== count($headers)) {
                $this->warning("Skipping row $rowIndex. Column count mismatch.");
                continue;
            }

            $item = array_combine($headers, $row);
            if (!is_array($item)) {         // @phpstan-ignore-line
                $this->warning("Skipping row $rowIndex. Failed to map headers.");
                continue;
            }

            $email = $item[$fieldMapping['email']] ?? null;

            if (!is_string($email)) {
                $this->warning("Skipping row $rowIndex. Email not string.");
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warning("Skipping row $rowIndex. Invalid email: $email");
                continue;
            }

            $lists = [];
            if ($fieldMapping['lists'] !== null && isset($item[$fieldMapping['lists']])) {
                /** @var string[] $lists */
                $lists = json_decode($item[$fieldMapping['lists']], true) ?? [];
            }

            $metadata = [];
            if (!empty($metaFields)) {
                foreach ($metaFields as $key => $value) {
                    if (in_array($key, $metaKeys, true) && $item[$value] !== null) {
                        $metadata[$key] = $item[$value];
                    }
                }
            }

            $subscribers[] = new ImportingSubscriberDto(
                email: $email,
                lists: $lists,
                status: SubscriberStatus::SUBSCRIBED,
                subscribedAt: $fieldMapping['subscribed_at'] && isset($item[$fieldMapping['subscribed_at']]) ? new \DateTimeImmutable($item[$fieldMapping['subscribed_at']]) : null,
                subscribeIp: $fieldMapping['subscribe_ip'] && isset($item[$fieldMapping['subscribe_ip']]) ? $item[$fieldMapping['subscribe_ip']] : null,
                metadata: $metadata
            );
        }

        fclose($stream);

        return new ArrayCollection($subscribers);
    }
}
