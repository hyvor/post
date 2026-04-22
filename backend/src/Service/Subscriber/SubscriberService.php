<?php

namespace App\Service\Subscriber;

use App\Entity\Media;
use App\Entity\Newsletter;
use App\Entity\NewsletterList;
use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\SubscriberExport;
use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use App\Entity\Type\SubscriberExportStatus;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\Event\SubscriberCreatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatingEvent;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SubscriberService
{

    use ClockAwareTrait;

    public const BULK_SUBSCRIBER_LIMIT = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository $subscriberRepository,
        private EventDispatcherInterface $ed,
        private MessageBusInterface $messageBus,
    ) {}

    /**
     * @param array<NewsletterList> $lists
     * @param array<string, scalar> $metadata
     */
    public function createSubscriber(
        Newsletter $newsletter,
        string $email,
        array $lists,
        SubscriberStatus $status,
        SubscriberSource $source,
        ?string $subscribeIp = null,
        ?\DateTimeImmutable $subscribedAt = null,
        array $metadata = [],
        bool $sendConfirmationEmail = true,
    ): Subscriber {
        $subscriber = new Subscriber()
            ->setNewsletter($newsletter)
            ->setEmail($email)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setStatus($status)
            ->setSubscribedAt($subscribedAt)
            ->setSubscribeIp($subscribeIp)
            ->setSource($source)
            ->setMetadata($metadata);

        // if status is subscribed, subscribed_at should be set to now
        if ($status === SubscriberStatus::SUBSCRIBED) {
            $subscriber->setSubscribedAt($subscribedAt ?? $this->now());
        }

        foreach ($lists as $list) {
            $subscriber->addList($list);
        }

        $this->em->persist($subscriber);
        $this->em->flush();

        $this->ed->dispatch(new SubscriberCreatedEvent($subscriber, $sendConfirmationEmail));

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
        $qb
            ->delete(Subscriber::class, 's')
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
        int $offset,
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
            $qb
                ->andWhere('s.status = :status')
                ->setParameter('status', $status->value);
        }

        if ($listId !== null) {
            $qb
                ->andWhere('l.id = :listId')
                ->andWhere('l.deleted_at IS NULL')
                ->setParameter('listId', $listId);
        }

        if ($search !== null) {
            $qb
                ->andWhere('s.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // dd($qb->getQuery()->getSQL());
        /** @var Subscriber[] $results */
        $results = $qb->getQuery()->getResult();

        return new ArrayCollection($results);
    }

    public function updateSubscriber(
        Subscriber $subscriber,
        UpdateSubscriberDto $updates,

        // if some lists are being removed, set the reason to correctly record
        // it in ListRemovalListener
        ListRemovalReason $listRemovalReason = ListRemovalReason::UNSUBSCRIBE,
        // whether to send the confirmation email if the status was changed to "pending"
        bool $sendConfirmationEmail = false,
    ): Subscriber {
        $subscriberOld = clone $subscriber;

        if ($updates->has('status')) {
            $subscriber->setStatus($updates->status);
        }

        if ($updates->has('source')) {
            $subscriber->setSource($updates->source);
        }

        if ($updates->has('subscribeIp')) {
            $subscriber->setSubscribeIp($updates->subscribeIp);
        }

        if ($updates->has('subscribedAt')) {
            $subscriber->setSubscribedAt($updates->subscribedAt);
        }

        if ($updates->has('metadata')) {
            $subscriber->setMetadata($updates->metadata);
        }

        if ($updates->has('lists')) {
            $subscriber->setLists(new ArrayCollection($updates->lists));
        }

        $subscriber->setUpdatedAt($this->now());

        $this->em->wrapInTransaction(function () use ($subscriberOld, $subscriber, $listRemovalReason) {
            $this->em->persist($subscriber);
            $this->ed->dispatch(
                new SubscriberUpdatingEvent(
                    $subscriberOld,
                    $subscriber,
                    listRemovalReason: $listRemovalReason,
                ),
            );
            $this->em->flush();
        });

        $this->ed->dispatch(
            new SubscriberUpdatedEvent(
                $subscriberOld,
                $subscriber,
                $sendConfirmationEmail,
            ),
        );

        return $subscriber;
    }

    public function getSubscriberByEmail(Newsletter $newsletter, string $email): ?Subscriber
    {
        return $this->subscriberRepository->findOneBy(['newsletter' => $newsletter, 'email' => $email]);
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
        string $errorMessage,
    ): void {
        $subscriberExport->setStatus(SubscriberExportStatus::FAILED);
        $subscriberExport->setErrorMessage($errorMessage);
        $this->em->persist($subscriberExport);
        $this->em->flush();
    }

    public function markSubscriberExportAsCompleted(
        SubscriberExport $subscriberExport,
        Media $media,
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
        return $this->em
            ->getRepository(SubscriberExport::class)
            ->findBy(['newsletter' => $newsletter], ['created_at' => 'DESC']);
    }


    public function addSubscriberToList(
        Subscriber $subscriber,
        NewsletterList $list,
    ): void {
        $subscriber->addList($list);
        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();
    }

    public function removeSubscriberFromList(
        Subscriber $subscriber,
        NewsletterList $list,
        bool $recordUnsubscription,
    ): void {
        $subscriber->removeList($list);
        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();


        if ($recordUnsubscription) {
            $existing = $this->em->getRepository(SubscriberListRemoval::class)->findOneBy([
                'list' => $list,
                'subscriber' => $subscriber,
            ]);

            if ($existing === null) {
                $unsubscribed = new SubscriberListRemoval()
                    ->setList($list)
                    ->setSubscriber($subscriber)
                    ->setCreatedAt($this->now());

                $this->em->persist($unsubscribed);
                $this->em->persist($list); // test fails otherwise, since this is used in removeElement, but sure why
                $this->em->flush();
            }
        }
    }

    public function hasSubscriberUnsubscribedFromList(Subscriber $subscriber, NewsletterList $list): bool
    {
        $record = $this->em->getRepository(SubscriberListRemoval::class)->findOneBy([
            'list' => $list,
            'subscriber' => $subscriber,
        ]);

        return $record !== null;
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
