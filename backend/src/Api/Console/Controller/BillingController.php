<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListenerOld;
use App\Service\Issue\SendService;
use Hyvor\Internal\Billing\License\PostLicense;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;

class BillingController extends AbstractController
{

    public function __construct(
        private SendService $sendService,
    ) {}

    #[Route('/billing/usage', methods: 'GET')]
    #[OrgEndpoint]
    public function getUsage(Request $request, BillingInterface $billing): JsonResponse
    {
        $organization = AuthorizationListenerOld::getOrganization($request);

        /** @var ?PostLicense $license */
        $license = $billing->license($organization->id)->license;

        return new JsonResponse([
            'emails' => [
                'limit' => $license->emails ?? 0,
                'this_month' => $this->sendService->getSendsCountThisMonthOfOrganization($organization->id),
                'last_12_months' => $this->sendService->getSendsCountLast12MonthsOfOrganization($organization->id),
            ],
        ]);
    }

}
