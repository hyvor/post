<?php

namespace App\Service\App\Messenger;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

class ClearWorkerMemoryEventListener
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    private function clearMemory(): void
    {
        $this->em->clear();
    }

    #[AsEventListener]
    public function onHandled(WorkerMessageHandledEvent $event): void
    {
        $this->clearMemory();
    }

    /**
     * @codeCoverageIgnore
     */
    #[AsEventListener]
    public function onFailed(WorkerMessageFailedEvent $event): void
    {
        $this->clearMemory();
    }
}