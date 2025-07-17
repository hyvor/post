<?php

namespace App\Service\Import\Parser;

use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Media\MediaService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CsvParser extends ParserAbstract
{
    public function __construct(
        private MediaService $mediaService
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
        $stream = $this->mediaService->getMediaStream($subscriberImport->getMedia()); // handle error

        if (!is_resource($stream)) {
            throw new ParserException('Unable to read media stream.');
        }

        $headers = fgetcsv($stream);
        if ($headers === false) {
            fclose($stream);
            throw new ParserException('CSV header row missing or invalid.');
        }

        $subscribers = [];
        $rowIndex = 1;

        while (($row = fgetcsv($stream)) !== false) {
            $rowIndex++;

            if (count($row) !== count($headers)) {
                $this->warning("Skipping row $rowIndex. Column count mismatch.");
                continue;
            }

            $item = array_combine($headers, $row);
            if (!is_array($item)) {
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

            $subscribers[] = new ImportingSubscriberDto(
                email: $email,
                lists: $fieldMapping['lists'] && isset($item[$fieldMapping['lists']]) ? json_decode($item[$fieldMapping['lists']], true) ?? [] : [],
                status: SubscriberStatus::SUBSCRIBED,
                subscribedAt: $fieldMapping['subscribed_at'] && isset($item[$fieldMapping['subscribed_at']]) ? new \DateTimeImmutable($item[$fieldMapping['subscribed_at']]) : null,
                subscribeIp: $fieldMapping['subscribe_ip'] && isset($item[$fieldMapping['subscribe_ip']]) ? $item[$fieldMapping['subscribe_ip']] : null,
            );
        }

        fclose($stream);

        return new ArrayCollection($subscribers);
    }
}
