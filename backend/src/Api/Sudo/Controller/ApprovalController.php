<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Input\Approval\ApproveInput;
use App\Api\Sudo\Object\ApprovalObject;
use App\Service\Approval\ApprovalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApprovalController extends AbstractController
{
    public function __construct(
        private ApprovalService $approvalService
    ) {}

    #[Route('/approvals', methods: ['GET'])]
    public function getApprovals(): JsonResponse
    {
        return new JsonResponse(array_map(
            fn($approval) => get_object_vars(new ApprovalObject($approval)),
            $this->approvalService->getApprovals()
        ));
    }

    #[Route('/approvals/{id}', methods: ['POST'])]
    public function approve(string $id, #[MapRequestPayload] ApproveInput $input): JsonResponse
    {
        $id = intval($id);
        $approval = $this->approvalService->getApporvalById($id);

        if ($approval === null) {
            throw new UnprocessableEntityHttpException('Approval not found');
        }

        $this->approvalService->updateStatusById($approval, $input->status);

        return new JsonResponse();
    }
}
