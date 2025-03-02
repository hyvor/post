<?php

namespace App\Service\Subscriber;

use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Enum\SubscriberSource;
use App\Enum\SubscriberStatus;
use App\Repository\SubscriberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SubscriberService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository $subscriberRepository
    )
    {
    }

    /**
     * @param iterable<NewsletterList> $lists
     */
    public function createSubscriber(
        Project $project,
        string $email,
        iterable $lists,
        SubscriberStatus $status,
        SubscriberSource $source,
        ?string $subscribeIp = null,
        ?\DateTimeImmutable $subscribedAt = null,
        ?\DateTimeImmutable $unsubscribedAt = null
    ): Subscriber
    {

        $subscriber = new Subscriber()
            ->setProject($project)
            ->setEmail($email)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setStatus($status)
            ->setSource($source);

        // if status is subscribed, subscribed_at should be set to now
        // if status is unsubscribed, unsubscribed_at should be set to now
        if ($status === SubscriberStatus::SUBSCRIBED) {
            $subscriber->setSubscribedAt($this->now());
        } elseif ($status === SubscriberStatus::UNSUBSCRIBED) {
            $subscriber->setUnsubscribedAt($this->now());
        }

        if ($subscribedAt !== null) {
            $subscriber->setSubscribedAt($subscribedAt);
        }
        if ($unsubscribedAt !== null) {
            $subscriber->setUnsubscribedAt($unsubscribedAt);
        }
        if ($subscribeIp !== null) {
            $subscriber->setSubscribeIp($subscribeIp);
        }

        foreach ($lists as $list) {
            $subscriber->addList($list);
        }

        $this->em->persist($subscriber);
        $this->em->flush();

        return $subscriber;
    }

    public function deleteSubscriber(Subscriber $subscriber): void
    {
        $this->em->remove($subscriber);
        $this->em->flush();
    }

    /**
     * @return ArrayCollection<int, Subscriber>
     */
    public function getSubscribers(
        Project $project,
        int $limit,
        int $offset
    ): ArrayCollection
    {
        return new ArrayCollection(
            $this->subscriberRepository->findBy(
                ['project' => $project],
                limit: $limit,
                offset: $offset
            )
        );
    }

    /**
     * @param array<NewsletterList> $lists
     */
    public function updateSubscriber(Subscriber $subscriber, string $email, array $lists, string $status): Subscriber
    {
        $subscriber->setEmail($email);
        $status_enum = SubscriberStatus::tryFrom($status);
        if ($status_enum === null) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $subscriber->setStatus($status_enum);

        // Clear lists
        foreach ($subscriber->getLists() as $list) {
            $subscriber->removeList($list);
        }
        foreach ($lists as $list) {
            $subscriber->addList($list);
        }
        $subscriber->setUpdatedAt($this->now());
        $this->em->persist($subscriber);
        $this->em->flush();

        return $subscriber;
    }
}
