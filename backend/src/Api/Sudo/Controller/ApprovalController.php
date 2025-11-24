<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Input\Approval\ApproveInput;
use App\Api\Sudo\Object\ApprovalObject;
use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
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
    )
    {
    }

    #[Route('/approvals', methods: ['GET'])]
    public function getApprovals(
        Request $request,
    ): JsonResponse
    {
        $userId = $request->query->has('user_id') ? $request->query->getInt('user_id') : null;
        $status = $request->query->has('status') ? ApprovalStatus::from($request->query->getString('status')) : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        return new JsonResponse(
            array_map(
                fn($approval) => new ApprovalObject($approval),
                $this->approvalService->getApprovals($userId, $status, limit: $limit, offset: $offset)
            )
        );
    }

    #[Route('/approvals/{id}', methods: ['POST'])]
    public function approve(Approval $approval, #[MapRequestPayload] ApproveInput $input): JsonResponse
    {
        $approval = $this->approvalService->approvalSudoAction(
            $approval,
            $input->status,
            $input->public_note,
            $input->private_note
        );

        return new JsonResponse(new ApprovalObject($approval));
    }
}
