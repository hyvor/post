<?php

namespace App\Api\Console\Input\Subscriber;

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

    public SubscriberStatus $status = SubscriberStatus::SUBSCRIBED;

    public ?SubscriberSource $source = null;

    #[Assert\Ip(version: Assert\Ip::ALL_ONLY_PUBLIC)]
    private ?string $subscribe_ip;

    private ?int $subscribed_at;

    private ?int $unsubscribed_at;

    /**
     * @var array<string,string>|null
     */
    public ?array $metadata = null;


    // settings

    public ListsStrategy $lists_strategy = ListsStrategy::SYNC;

    #[Assert\All(new Assert\Choice(callback: 'getListResubscribeOnValues'))]
    private array $list_skip_resubscribe_on = ['unsubscribe', 'bounce'];

    public ListRemoveReason $list_remove_reason = ListRemoveReason::UNSUBSCRIBE;

    public MetadataStrategy $metadata_strategy = MetadataStrategy::MERGE;

    public bool $send_pending_confirmation_email = false;

    public function getSubscriberIp(): ?string
    {
        return $this->has('subscribe_ip') ? $this->subscribe_ip : null;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        $subscribedAt = $this->has('subscribed_at') ? $this->subscribed_at : null;
        return $subscribedAt ? new \DateTimeImmutable()->setTimestamp($this->subscribed_at) : null;
    }

    public function getUnsubscribedAt(): ?\DateTimeImmutable
    {
        $unsubscribedAt = $this->has('unsubscribed_at') ? $this->unsubscribed_at : null;
        return $unsubscribedAt ? new \DateTimeImmutable()->setTimestamp($this->unsubscribed_at) : null;
    }

    /**
     * @return ListSkipResubscribeOn[]
     */
    public function getListSkipResubscribeOn(): array
    {
        $listSkipResubscribeOn = $this->has('list_skip_resubscribe_on') ? $this->list_skip_resubscribe_on : [];
        return array_map(fn($item) => ListSkipResubscribeOn::tryFrom($item), $listSkipResubscribeOn);
    }

    /**
     * @return string[]
     */
    public function getListResubscribeOnValues(): array
    {
        return array_map(fn($value) => $value->value, ListSkipResubscribeOn::cases());
    }

}
