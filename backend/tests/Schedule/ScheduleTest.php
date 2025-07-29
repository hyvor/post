<?php

namespace App\Tests\Schedule;

use App\Schedule\Schedule;
use App\Service\Subscriber\Message\ClearPendingSubscribersMessage;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Schedule::class)]
class ScheduleTest extends KernelTestCase
{
    public function test_schedule(): void
    {
        $schedule = new Schedule();
        $s = $schedule->getSchedule();
        $messages = $s->getRecurringMessages();
        $this->assertCount(1, $messages);
    }
}
