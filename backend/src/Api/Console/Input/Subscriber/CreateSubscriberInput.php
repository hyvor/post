<?php

namespace App\Api\Console\Input\Subscriber;

use App\Entity\Type\ListRemovalReason;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Validator\Constraints as Assert;

class CreateSubscriberInput
{

    use OptionalPropertyTrait;
    use ClockAwareTrait;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;

    /**
     * @var ?(int|string)[]
     */
    public ?array $lists = null;

    public ?SubscriberStatus $status = null;

    public ?SubscriberSource $source = null;

    #[Assert\Ip(version: Assert\Ip::ALL_ONLY_PUBLIC)]
    public ?string $subscribe_ip;

    public ?int $subscribed_at;

    /**
     * @var array<string, scalar>|null
     */
    #[Assert\All(new Assert\Type('scalar'))]
    public ?array $metadata = null;

    // settings

    public ListsStrategy $lists_strategy = ListsStrategy::MERGE;

    /**
     * @var string[]
     */
    #[Assert\All(new Assert\Choice(callback: 'getListResubscribeOnValues'))]
    public array $list_skip_resubscribe_on = ['unsubscribe', 'bounce', 'complaint'];

    public ListRemovalReason $list_removal_reason = ListRemovalReason::UNSUBSCRIBE;

    public MetadataStrategy $metadata_strategy = MetadataStrategy::MERGE;

    public bool $send_pending_confirmation_email = false;

    public function getSubscribeIp(): ?string
    {
        return $this->has('subscribe_ip') ? $this->subscribe_ip : null;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        $subscribedAt = $this->has('subscribed_at') ? $this->subscribed_at : null;
        return $subscribedAt ? new \DateTimeImmutable()->setTimestamp($this->subscribed_at) : null;
    }

    /**
     * @return ListRemovalReason[]
     */
    public function getListSkipResubscribeOn(): array
    {
        return array_map(fn($item) => ListRemovalReason::from($item), $this->list_skip_resubscribe_on);
    }

    /**
     * @return string[]
     */
    public function getListResubscribeOnValues(): array
    {
        return array_map(fn($value) => $value->value, ListRemovalReason::cases());
    }

}
