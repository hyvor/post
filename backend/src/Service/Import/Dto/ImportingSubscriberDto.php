<?php

namespace App\Service\Import\Dto;

use App\Entity\Type\SubscriberStatus;

class ImportingSubscriberDto
{

    public function __construct(
        public string $email,
        /** @var int[] $lists */
        public array $lists,
        public SubscriberStatus $status,
        public ?\DateTimeImmutable $subscribedAt,
        public ?string $subscribeIp = null,
        /** @var array<string, string> | null $metadata */
        public ?array $metadata = null
    ) {
    }


}
