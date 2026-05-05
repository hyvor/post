<?php

namespace App\Service\Subscriber\MessageHandler;

use App\Entity\Subscriber;
use App\Entity\Type\ListRemovalReason;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\Message\UnsubscribeByEmailMessage;
use App\Service\Subscriber\SubscriberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UnsubscribeByEmailMessageHandler
{
    private const CHUNK_SIZE = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberService $subscriberService,
    ) {}

    public function __invoke(UnsubscribeByEmailMessage $message): void
    {
        $offset = 0;

        while (true) {
            /** @var Subscriber[] $subscribers */
            $subscribers = $this->em->createQueryBuilder()
                ->select('s')
                ->from(Subscriber::class, 's')
                ->where('s.email = :email')
                ->setParameter('email', $message->email)
                ->orderBy('s.id', 'ASC')
                ->setMaxResults(self::CHUNK_SIZE)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            if (empty($subscribers)) {
                break;
            }

            foreach ($subscribers as $subscriber) {
                $dto = new UpdateSubscriberDto();
                $dto->lists = [];
                $dto->unsubscribeReason = $message->reason;

                $this->subscriberService->updateSubscriber(
                    $subscriber,
                    $dto,
                    listRemovalReason: ListRemovalReason::UNSUBSCRIBE,
                );
            }

            if (count($subscribers) < self::CHUNK_SIZE) {
                break;
            }

            $offset += self::CHUNK_SIZE;
            $this->em->clear();
        }
    }
}
