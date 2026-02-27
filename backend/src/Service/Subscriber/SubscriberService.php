<?php

namespace App\Service\Subscriber;

use App\Entity\Media;
use App\Entity\Newsletter;
use App\Entity\NewsletterList;
use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\SubscriberExport;
use App\Entity\SubscriberListUnsubscribed;
use App\Entity\Type\SubscriberExportStatus;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\Subscriber\Message\SubscriberCreatedMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class SubscriberService
{

    use ClockAwareTrait;

    public const BULK_SUBSCRIBER_LIMIT = 100;

    public function __construct(
        private EntityManagerInterface   $em,
        private SubscriberRepository     $subscriberRepository,
        private MessageBusInterface      $messageBus,
    )
    {
    }

    /**
     * @param iterable<NewsletterList> $lists
     */
    public function createSubscriber(
        Newsletter          $newsletter,
        string              $email,
        iterable            $lists,
        SubscriberStatus    $status,
        SubscriberSource    $source,
        ?string             $subscribeIp = null,
        ?\DateTimeImmutable $subscribedAt = null,
        ?\DateTimeImmutable $unsubscribedAt = null
    ): Subscriber
    {
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

        $this->messageBus->dispatch(new SubscriberCreatedMessage($subscriber->getId()));

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
        Newsletter        $newsletter,
        ?SubscriberStatus $status,
        ?int              $listId,
        ?string           $search,
        int               $limit,
        int               $offset
    ): ArrayCollection
    {
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
        if ($updates->has('email')) {
            $subscriber->setEmail($updates->email);
        }

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

        if ($updates->has('optInAt')) {
            $subscriber->setOptInAt($updates->optInAt);
        }

        if ($updates->has('unsubscribedAt')) {
            $subscriber->setUnsubscribedAt($updates->unsubscribedAt);
        }

        if ($updates->has('unsubscribedReason')) {
            $subscriber->setUnsubscribeReason($updates->unsubscribedReason);
        }

        if ($updates->has('metadata')) {
            $metadata = $subscriber->getMetadata();
            foreach ($updates->metadata as $key => $value) {
                $metadata[$key] = $value;
            }
            $subscriber->setMetadata($metadata);
        }

        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();

        return $subscriber;
    }

    public function getSubscriberByEmail(Newsletter $newsletter, string $email): ?Subscriber
    {
        return $this->subscriberRepository->findOneBy(['newsletter' => $newsletter, 'email' => $email]);
    }

    public function unsubscribeBySend(
        Send                $send,
        ?\DateTimeImmutable $at = null,
        ?string             $reason = null
    ): void
    {
        $subscriber = $send->getSubscriber();

        $update = new UpdateSubscriberDto();

        $update->status = SubscriberStatus::UNSUBSCRIBED;
        $update->optInAt = null;
        $update->unsubscribedAt = $at ?? $this->now();
        $update->unsubscribedReason = $reason;

        $this->updateSubscriber($subscriber, $update);
    }

    public function unsubscribeByEmail(
        string              $email,
        ?\DateTimeImmutable $at = null,
        ?string             $reason = null
    ): void
    {
        $qb = $this->em->createQueryBuilder();

        $qb->update(Subscriber::class, 's')
            ->set('s.status', ':status')
            ->set('s.opt_in_at', ':optInAt')
            ->set('s.unsubscribed_at', ':unsubscribedAt')
            ->set('s.unsubscribe_reason', ':reason')
            ->where('s.email = :email')
            ->setParameter('status', SubscriberStatus::UNSUBSCRIBED->value)
            ->setParameter('optInAt', null)
            ->setParameter('unsubscribedAt', $at ?? $this->now())
            ->setParameter('reason', $reason)
            ->setParameter('email', $email);

        $qb->getQuery()->execute();
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
        string           $errorMessage
    ): void
    {
        $subscriberExport->setStatus(SubscriberExportStatus::FAILED);
        $subscriberExport->setErrorMessage($errorMessage);
        $this->em->persist($subscriberExport);
        $this->em->flush();
    }

    public function markSubscriberExportAsCompleted(
        SubscriberExport $subscriberExport,
        Media            $media
    ): void
    {
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

    /**
     * @param Subscriber $subscriber
     * @param NewsletterList[] $lists
     */
    public function setSubscriberLists(
        Subscriber $subscriber,
        array $lists
    ): void
    {

        $listIds = array_map(fn(NewsletterList $list) => $list->getId(), $lists);

        // remove lists that are not in the new list
        foreach ($subscriber->getLists() as $existingList) {
            if (!in_array($existingList->getId(), $listIds)) {
                $subscriber->removeList($existingList);
            }
        }

        // add new lists
        foreach ($lists as $list) {
            if (!$subscriber->getLists()->contains($list)) {
                $subscriber->addList($list);
            }
        }

        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();

    }

    public function addSubscriberToList(
        Subscriber     $subscriber,
        NewsletterList $list,
    ): void
    {
        $subscriber->addList($list);
        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();
    }

    public function removeSubscriberFromList(
        Subscriber     $subscriber,
        NewsletterList $list,
        bool           $recordUnsubscription
    ): void
    {
        $subscriber->removeList($list);
        $subscriber->setUpdatedAt($this->now());

        $this->em->persist($subscriber);
        $this->em->flush();


        if ($recordUnsubscription) {
            $existing = $this->em->getRepository(SubscriberListUnsubscribed::class)->findOneBy([
                'list' => $list,
                'subscriber' => $subscriber,
            ]);

            if ($existing === null) {
                $unsubscribed = new SubscriberListUnsubscribed()
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
        $record = $this->em->getRepository(SubscriberListUnsubscribed::class)->findOneBy([
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
