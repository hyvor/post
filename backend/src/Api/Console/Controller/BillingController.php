<?php

namespace App\Api\Console\Controller;

use App\Service\Issue\SendService;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class BillingController extends AbstractController
{

    use HasHyvorUser;

    public function __construct(
        private SendService $sendService
    )
    {
    }

    #[Route('/billing/usage', methods: 'GET')]
    public function getUsage(): JsonResponse
    {
        $user = $this->getHyvorUser();

        return new JsonResponse([
            'emails' => [
                'this_month' => $this->sendService->getSendsCountThisMonthOfUser($user->id),
                'last_12_months' => $this->sendService->getSendsCountLast12MonthsOfUser($user->id),
            ]
        ]);

    }

}