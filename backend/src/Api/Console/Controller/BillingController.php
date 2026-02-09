<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\OrganizationLevelEndpoint;
use App\Service\Issue\SendService;
use Hyvor\Internal\Billing\License\PostLicense;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Billing\BillingInterface;

class BillingController extends AbstractController
{

    public function __construct(
        private SendService $sendService
    )
    {
    }

    #[Route('/billing/usage', methods: 'GET')]
    #[OrganizationLevelEndpoint]
    public function getUsage(Request $request, BillingInterface $billing): JsonResponse
    {
        $organization = AuthorizationListener::getOrganization($request);

        /** @var ?PostLicense $license */
        $license = $billing->license($organization->id)->license;

        return new JsonResponse([
            'emails' => [
                'limit' => $license->emails ?? 0,
                'this_month' => $this->sendService->getSendsCountThisMonthOfOrganization($organization->id),
                'last_12_months' => $this->sendService->getSendsCountLast12MonthsOfOrganization($organization->id),
            ]
        ]);
    }

}
