<?php

namespace App\Service\Subscriber;

use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Enum\SubscriberSource;
use App\Enum\SubscriberStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SubscriberService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @param array<NewsletterList> $lists
     */
    public function createSubscriber(
        Project $project,
        string $email,
        array $lists,
        string $status,
        string $source,
        ?string $subscribe_ip = null,
        ?\DateTimeImmutable $subscribed_at = null,
        ?\DateTimeImmutable $unsubscribed_at = null
    ): Subscriber
    {
        $status_enum = SubscriberStatus::tryFrom($status);
        if ($status_enum === null) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $source_enum = SubscriberSource::tryFrom($source);
        if ($source_enum === null) {
            throw new \InvalidArgumentException('Invalid source');
        }

        $subscriber = new Subscriber()
            ->setProject($project)
            ->setEmail($email)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setStatus($status_enum)
            ->setSource($source_enum);

        // if status is subscribed, subscribed_at should be set to now
        // if status is unsubscribed, unsubscribed_at should be set to now
        if ($status_enum === SubscriberStatus::SUBSCRIBED) {
            $subscriber->setSubscribedAt($this->now());
        } elseif ($status_enum === SubscriberStatus::UNSUBSCRIBED) {
            $subscriber->setUnsubscribedAt($this->now());
        }

        if ($subscribed_at !== null) {
            $subscriber->setSubscribedAt($subscribed_at);
        }
        if ($unsubscribed_at !== null) {
            $subscriber->setUnsubscribedAt($unsubscribed_at);
        }
        if ($subscribe_ip !== null) {
            $subscriber->setSubscribeIp($subscribe_ip);
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
    public function getSubscribers(Project $project): ArrayCollection
    {
        return new ArrayCollection($this->em->getRepository(Subscriber::class)->findBy(['project' => $project]));
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
