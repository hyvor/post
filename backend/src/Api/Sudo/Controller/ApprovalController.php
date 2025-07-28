<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Input\Approval\ApproveInput;
use App\Api\Sudo\Input\Approval\GetApprovalsInput;
use App\Api\Sudo\Object\ApprovalObject;
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
    ) {}

    #[Route('/approvals', methods: ['GET'])]
    public function getApprovals(
        Request $request,
    ): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        return new JsonResponse(
            array_map(
                fn($approval) => new ApprovalObject($approval),
                $this->approvalService->getApprovals(limit: $limit, offset: $offset)
            )
        );
    }

    #[Route('/approvals/{id}', methods: ['POST'])]
    public function approve(string $id, #[MapRequestPayload] ApproveInput $input): JsonResponse
    {
        $id = intval($id);
        $approval = $this->approvalService->getApporvalById($id);

        if ($approval === null) {
            throw new UnprocessableEntityHttpException('Approval request not found');
        }

        $approval = $this->approvalService->changeStatus(
            $approval,
            $input->status,
            $input->public_note,
            $input->private_note
        );

        return new JsonResponse(new ApprovalObject($approval));
    }
}
