<?php

namespace App\Api\Sudo\Controller;

use App\Service\Approval\ApprovalService;
use App\Service\Import\ImportService;
use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class SudoController extends AbstractController
{

    public function __construct(
        private InternalConfig  $internalConfig,
        private ApprovalService $approvalService,
        private ImportService   $importService,
    )
    {
    }

    #[Route('/init', methods: 'GET')]
    public function initSudo(): JsonResponse
    {
        return new JsonResponse([
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance()
                ]
            ],
            'stats' => [
                'reviewing_approvals' => $this->approvalService->getReviewingApprovalsCount(),
                'pending_imports' => $this->importService->getPendingSubscriberImportsCount(),
            ]
        ]);
    }
}
