<?php

namespace App\Service\Billing\Usage;

use App\Service\Issue\SendService;
use App\Service\Newsletter\NewsletterService;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use function PHPUnit\Framework\assertNotNull;

class EmailsUsage extends UsageAbstract
{

    public function __construct(
        private SendService       $sendService,
        private NewsletterService $newsletterService
    )
    {
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
        $newsletter = $this->newsletterService->getNewsletterById($resourceId);
        assertNotNull($newsletter);

        return $this->sendService->getSendsCountThisMonthOfNewsletter($newsletter);
    }

}