<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\Subscriber;
use App\Entity\NewsletterList;
use App\Entity\Type\SubscriberSource;
use App\Service\Import\Subscriber\ImportingSubscriberDto;
use App\Entity\SubscriberImport;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\Subscriber\CsvParser;
use App\Service\Media\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportSubscribersMessageHandler
{
    use ClockAwareTrait;
    public function __construct(
        private EntityManagerInterface $em,
        private MediaService $mediaService,
    ) {
    }

    public function __invoke(ImportSubscribersMessage $message): void
    {
        $subscriberImport = $this->em->getRepository(SubscriberImport::class)->find($message->getSubscriberImportId());
        assert($subscriberImport !== null);

        $parser = new CsvParser($subscriberImport, $this->mediaService);
        $subscribers = $parser->parse();

        $newsletter = $subscriberImport->getNewsletter();

        foreach ($subscribers as $dto) {
            assert($dto instanceof ImportingSubscriberDto);
            $subscriber = new Subscriber();
            $subscriber->setNewsletter($newsletter);
            $subscriber->setEmail($dto->email);
            $subscriber->setStatus($dto->status);
            $subscriber->setSubscribedAt($dto->subscribedAt ?? $this->now());
            $subscriber->setSubscribeIp($dto->subscribeIp);
            $subscriber->setSource(SubscriberSource::IMPORT);
            $subscriber->setCreatedAt($this->now());
            $subscriber->setUpdatedAt($this->now());
            // Add lists
            foreach ($dto->lists as $listId) {
                $list = $this->em->getRepository(NewsletterList::class)->find($listId);
                if ($list) {
                    $subscriber->addList($list);
                }
            }
            $this->em->persist($subscriber);
        }
        $this->em->flush();
    }

}
