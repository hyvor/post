<?php

namespace App\Api\Console\Controller;

use App\Service\Issue\SendService;
use Hyvor\Internal\Billing\License\PostLicense;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Billing\BillingInterface;

class BillingController extends AbstractController
{

    public function __construct(
        private SendService $sendService
    ) {
    }

    #[Route('/billing/usage', methods: 'GET')]
    public function getUsage(BillingInterface $billing): JsonResponse
    {
        $user = $this->getHyvorUser();

        /** @var ?PostLicense $license */
        $license = $billing->license($user->id, null);

        return new JsonResponse([
            'emails' => [
                'limit' => $license->emails ?? 0,
                'this_month' => $this->sendService->getSendsCountThisMonthOfUser($user->id),
                'last_12_months' => $this->sendService->getSendsCountLast12MonthsOfUser($user->id),
            ]
        ]);
    }

}