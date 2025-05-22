<?php

namespace App\Service\Billing\Usage;

use App\Service\Issue\SendService;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;

class EmailsUsage extends UsageAbstract
{

    public function __construct(
        private SendService $sendService,
    ) {
        parent::__construct();
    }

    public function getLicenseType(): string
    {
        return PostLicense::class;
    }

    public function getKey(): string
    {
        return 'emails';
    }

    public function usageOfUser(int $userId): int
    {
        return $this->sendService->getSendsCountThisMonthOfUser($userId);
    }

    public function usageOfResource(int $resourceId): int
    {
        return $this->sendService->getSendsCountThisMonthOfNewsletter($resourceId);
    }

}