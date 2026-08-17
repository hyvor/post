<?php

namespace App\Api\Console\Controller\Org;

use App\Service\Issue\SendService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class BillingController extends AbstractController
{

    public function __construct(
        private SendService $sendService,
    ) {}

    #[Route('/billing/usage', methods: 'GET')]
    #[OrgEndpoint]
    public function getUsage(ConsoleAuthResults $consoleAuth, BillingInterface $billing): JsonResponse
    {
        $organizationId = $consoleAuth->getOrganizationId();

        /** @var ?PostLicense $license */
        $license = $billing->license($organizationId)->license;

        return new JsonResponse([
            'emails' => [
                'limit' => $license->emails ?? 0,
                'this_month' => $this->sendService->getSendsCountThisMonthOfOrganization($organizationId),
                'last_12_months' => $this->sendService->getSendsCountLast12MonthsOfOrganization($organizationId),
            ],
        ]);
    }

}
