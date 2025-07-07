<?php

namespace App\Service\Subscriber;

use App\Entity\Media;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\SubscriberExport;
use App\Entity\Type\SubscriberExportStatus;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Event\Subscriber\CreateSubscriberEvent;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\UserInvite\EmailNotificationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

class SubscriberService
{

    use ClockAwareTrait;

    public const BULK_SUBSCRIBER_LIMIT = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository $subscriberRepository,
        private MessageBusInterface $messageBus,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param iterable<NewsletterList> $lists
     */
    public function createSubscriber(
        Newsletter $newsletter,
        string $email,
        iterable $lists,
        SubscriberStatus $status,
        SubscriberSource $source,
        ?string $subscribeIp = null,
        ?\DateTimeImmutable $subscribedAt = null,
        ?\DateTimeImmutable $unsubscribedAt = null
    ): Subscriber {
        $subscriber = new Subscriber()
            ->setNewsletter($newsletter)
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

        $event = new CreateSubscriberEvent($subscriber);
        $this->eventDispatcher->dispatch($event, CreateSubscriberEvent::class);

        return $subscriber;
    }

    public function deleteSubscriber(Subscriber $subscriber): void
    {
        $this->em->remove($subscriber);
        $this->em->flush();
    }

    /**
     * @param array<Subscriber> $subscribers
     */
    public function deleteSubscribers(array $subscribers): void
    {
        $ids = array_map(fn(Subscriber $s) => $s->getId(), $subscribers);

        $qb = $this->em->createQueryBuilder();
        $qb->delete(Subscriber::class, 's')
            ->where($qb->expr()->in('s.id', ':ids'))
            ->setParameter('ids', $ids);

        $qb->getQuery()->execute();
    }

    /**
     * @return ArrayCollection<int, Subscriber>
     */
    public function getSubscribers(
        Newsletter $newsletter,
        ?SubscriberStatus $status,
        ?int $listId,
        ?string $search,
        int $limit,
        int $offset
    ): ArrayCollection {
        $qb = $this->subscriberRepository->createQueryBuilder('s');

        $qb
            ->distinct()
            ->leftJoin('s.lists', 'l')
            ->where('s.newsletter = :newsletter')
            ->setParameter('newsletter', $newsletter)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($status !== null) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $status->value);
        }

        if ($listId !== null) {
            $qb->andWhere('l.id = :listId')
                ->andWhere('l.deleted_at IS NULL')
                ->setParameter('listId', $listId);
        }

        if ($search !== null) {
            $qb->andWhere('s.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // dd($qb->getQuery()->getSQL());
        /** @var Subscriber[] $results */
        $results = $qb->getQuery()->getResult();

        return new ArrayCollection($results);
    }

    public function updateSubscriber(Subscriber $subscriber, UpdateSubscriberDto $updates): Subscriber
    {
        if ($updates->hasProperty('email')) {
            $subscriber->setEmail($updates->email);
        }

        if ($updates->hasProperty('status')) {
            $subscriber->setStatus($updates->status);
        }

        if ($updates->hasProperty('lists')) {
            // Clear & re-add lists
            foreach ($subscriber->getLists() as $list) {
                $subscriber->removeList($list);
            }
            foreach ($updates->lists as $list) {
                $subscriber->addList($list);
            }
        }

        if ($updates->hasProperty('unsubscribedAt')) {
            $subscriber->setUnsubscribedAt($updates->unsubscribedAt);
        }

        if ($updates->hasProperty('unsubscribedReason')) {
            $subscriber->setUnsubscribeReason($updates->unsubscribedReason);
        }

        if ($updates->hasProperty('metadata')) {
            $metadata = $subscriber->getMetadata();
            foreach ($updates->metadata as $key => $value) {
                $metadata[$key] = $value;
            }
            $subscriber->setMetadata($metadata);
        }

        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();

        $event = new CreateSubscriberEvent($subscriber);
        $this->eventDispatcher->dispatch($event, CreateSubscriberEvent::class);

        return $subscriber;
    }

    /**
     * @param array<Subscriber> $subscribers
     */
    public function updateSubscribersStatus(array $subscribers, SubscriberStatus $status): void
    {
        $ids = array_map(fn(Subscriber $s) => $s->getId(), $subscribers);

        $qb = $this->em->createQueryBuilder();
        $qb->update(Subscriber::class, 's')
            ->set('s.status', ':status')
            ->where($qb->expr()->in('s.id', ':ids'))
            ->setParameter('status', $status->value)
            ->setParameter('ids', $ids);

        $qb->getQuery()->execute();
    }

    public function getSubscriberByEmail(Newsletter $newsletter, string $email): ?Subscriber
    {
        return $this->subscriberRepository->findOneBy(['newsletter' => $newsletter, 'email' => $email]);
    }

    public function unsubscribeBySend(
        Send $send,
        ?\DateTimeImmutable $at = null,
        ?string $reason = null
    ): void {
        $subscriber = $send->getSubscriber();

        $update = new UpdateSubscriberDto();

        $update->status = SubscriberStatus::UNSUBSCRIBED;
        $update->unsubscribedAt = $at ?? $this->now();
        $update->unsubscribedReason = $reason;

        $this->updateSubscriber($subscriber, $update);
    }

    public function exportSubscribers(Newsletter $newsletter): SubscriberExport
    {
        // Create a new SubscriberExport entity
        $subscriberExport = new SubscriberExport();
        $subscriberExport->setCreatedAt($this->now());
        $subscriberExport->setUpdatedAt($this->now());
        $subscriberExport->setNewsletter($newsletter);
        $subscriberExport->setStatus(SubscriberExportStatus::PENDING);

        $this->em->persist($subscriberExport);
        $this->em->flush();

        $this->messageBus->dispatch(new ExportSubscribersMessage($subscriberExport->getId()));

        return $subscriberExport;
    }

    public function markSubscriberExportAsFailed(
        SubscriberExport $subscriberExport,
        string $errorMessage
    ): void {
        $subscriberExport->setStatus(SubscriberExportStatus::FAILED);
        $subscriberExport->setErrorMessage($errorMessage);
        $this->em->persist($subscriberExport);
        $this->em->flush();
    }

    public function markSubscriberExportAsCompleted(
        SubscriberExport $subscriberExport,
        Media $media
    ): void {
        $subscriberExport->setStatus(SubscriberExportStatus::COMPLETED);
        $subscriberExport->setMedia($media);
        $this->em->persist($subscriberExport);
        $this->em->flush();
    }

    /**
     * @return array<SubscriberExport>
     */
    public function getExports(Newsletter $newsletter): array
    {
        return $this->em->getRepository(SubscriberExport::class)
            ->findBy(['newsletter' => $newsletter], ['created_at' => 'DESC']);
    }

    public function getSubscriberById(int $id): ?Subscriber
    {
        return $this->subscriberRepository->find($id);
    }

    /**
     * @return array<Subscriber>
     */
    public function getAllSubscribers(Newsletter $newsletter): array
    {
        // TODO: limit, offset needed
        return $this->subscriberRepository->findBy(['newsletter' => $newsletter], ['id' => 'DESC']);
    }
}
