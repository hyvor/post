<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\NewsletterList;
use App\Entity\Subscriber;
use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberSource;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\Parser\CsvParser;
use App\Service\NewsletterList\NewsletterListService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportSubscribersMessageHandler
{
    use ClockAwareTrait;
    public function __construct(
        private EntityManagerInterface $em,
        private NewsletterListService $newsletterListService,
        private CsvParser $parser
    ) {
    }

    public function __invoke(ImportSubscribersMessage $message): void
    {
        $subscriberImport = $this->em->getRepository(SubscriberImport::class)->find($message->getSubscriberImportId());
        assert($subscriberImport !== null);

        $subscribers = $this->parser->parse($subscriberImport);

        $newsletter = $subscriberImport->getNewsletter();
        $lists = $this->newsletterListService->getListsOfNewsletter($newsletter);

        foreach ($subscribers as $dto) {
            $subscriber = new Subscriber();
            $subscriber->setNewsletter($newsletter);
            $subscriber->setEmail($dto->email);
            $subscriber->setStatus($dto->status);
            $subscriber->setSubscribedAt($dto->subscribedAt ?? $this->now());
            $subscriber->setSubscribeIp($dto->subscribeIp);
            $subscriber->setMetadata($dto->metadata);
            $subscriber->setSource(SubscriberSource::IMPORT);
            $subscriber->setCreatedAt($this->now());
            $subscriber->setUpdatedAt($this->now());

            foreach ($dto->lists as $listId) {
                $list = $lists->findFirst(fn($key, $l) => $l->getId() === $listId);
                if ($list === null) {
                    continue;
                }
                $subscriber->addList($list);
            }

            $this->em->persist($subscriber);
        }
        $this->em->flush();
    }

}
