<?php

namespace App\Tests\Api\Console\Billing;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

class GetUsageTest extends WebTestCase
{

    public function test_get_usage(): void
    {
        Clock::set(new MockClock('2025-05-10'));

        // current user sends
        $currentUserProject = NewsletterFactory::createOne(['user_id' => 1]);
        $firstDayThisMonth = new \DateTimeImmutable('first day of this month')->modify('+1 day');
        SendFactory::createMany(3, [
            'project' => $currentUserProject,
            'created_at' => $firstDayThisMonth,
        ]);

        // not this month
        SendFactory::createMany(4, [
            'project' => $currentUserProject,
            'created_at' => $firstDayThisMonth->modify('-1 month'),
        ]);
        SendFactory::createMany(1, [
            'project' => $currentUserProject,
            'created_at' => $firstDayThisMonth->modify('-2 month'),
        ]);

        // other user
        $otherUserProject = NewsletterFactory::createOne(['user_id' => 2]);
        SendFactory::createMany(5, [
            'project' => $otherUserProject,
            'created_at' => $firstDayThisMonth,
        ]);

        $response = $this->consoleApi(
            null,
            'GET',
            '/billing/usage'
        );

        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson();
        $this->assertIsArray($json['emails']);
        $this->assertSame(3, $json['emails']['this_month']);

        $last12Months = $json['emails']['last_12_months'];
        $this->assertIsArray($last12Months);

        $this->assertSame([
            "2024-06" => 0,
            "2024-07" => 0,
            "2024-08" => 0,
            "2024-09" => 0,
            "2024-10" => 0,
            "2024-11" => 0,
            "2024-12" => 0,
            "2025-01" => 0,
            "2025-02" => 0,
            "2025-03" => 1,
            "2025-04" => 4,
            "2025-05" => 3,
        ], $last12Months);
    }

}
