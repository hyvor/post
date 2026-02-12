<?php

namespace App\Tests\Api\Console\Billing;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class GetUsageTest extends WebTestCase
{
    use ClockSensitiveTrait;

    public function test_get_usage(): void
    {
        $date = new \DateTimeImmutable('2025-05-10');
        static::mockTime($date);

        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, PostLicense::trial())]
        );

        // current user sends
        $currentUserNewsletter = NewsletterFactory::createOne(['organization_id' => 1]);
        $firstDayThisMonth = $date->modify('first day of this month')->modify('+1 day');
        SendFactory::createMany(3, [
            'newsletter' => $currentUserNewsletter,
            'created_at' => $firstDayThisMonth,
        ]);

        // not this month
        SendFactory::createMany(4, [
            'newsletter' => $currentUserNewsletter,
            'created_at' => $firstDayThisMonth->modify('-1 month'),
        ]);
        SendFactory::createMany(1, [
            'newsletter' => $currentUserNewsletter,
            'created_at' => $firstDayThisMonth->modify('-2 month'),
        ]);

        // other user
        $otherUserNewsletter = NewsletterFactory::createOne(['organization_id' => 2]);
        SendFactory::createMany(5, [
            'newsletter' => $otherUserNewsletter,
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
