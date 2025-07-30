<?php

namespace App\Schedule;

use App\Service\Subscriber\Message\ClearPendingSubscribersMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule(name: 'global')]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->add(RecurringMessage::every('1 day', new ClearPendingSubscribersMessage))
            ->lock($this->lockFactory->createLock('clear_pending_subscribers'));
    }
}
