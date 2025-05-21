<?php

namespace App\Service\Import\Subscriber;

use App\Entity\Type\SubscriberStatus;
use Doctrine\Common\Collections\Collection;

class JsonParser extends ParserAbstract
{

    public function __construct(
        private string $json,
    ) {
    }

    /**
     * @return Collection<int, ImportingSubscriberDto>
     * @throws ParserException
     */
    public function parse(): Collection
    {
        $data = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new ParserException('Invalid JSON data');
        }

        $subscribers = [];
        foreach ($data as $i => $item) {
            if (!is_array($item)) {
                $this->warning("Skipping invalid item at index $i. Expected array.");
                continue;
            }

            $email = $item['email'] ?? null;

            if (!is_string($email)) {
                $this->warning("Skipping item at index $i. Email not string.");
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warning("Skipping item at index $i. Invalid email: $email");
                continue;
            }

            $subscribers[] = new ImportingSubscriberDto(
                email: $email,
                lists: $item['lists'],
                status: SubscriberStatus::SUBSCRIBED,
                subscribedAt: isset($item['subscribed_at']) ? new \DateTimeImmutable($item['subscribed_at']) : null,
                subscribeIp: $item['subscribe_ip'] ?? null,
            );
        }


        return $subscribers;
    }

}