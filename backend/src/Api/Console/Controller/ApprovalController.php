<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Approval\CreateApprovalInput;
use App\Api\Console\Object\ApprovalObject;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApprovalController extends AbstractController
{
    use HasHyvorUser;

    public function __construct(
        private ApprovalService $approvalService
    ) {
    }

    #[Route('/approvals', methods: 'GET')]
    public function getApproval(): JsonResponse
    {
        $user = $this->getHyvorUser();
        $approval = $this->approvalService->getApprovalOfUser($user);

        if ($approval === null) {
            throw new UnprocessableEntityHttpException('No approval found for user');
        }
        return new JsonResponse(new ApprovalObject($approval));
    }

    #[Route('/approvals', methods: 'POST')]
    public function approve(
        #[MapRequestPayload] CreateApprovalInput $input
    ): JsonResponse
    {
        $user = $this->getHyvorUser();

        if ($this->approvalService->getApprovalStatusOfUser($user) === ApprovalStatus::APPROVED) {
            throw new UnprocessableEntityHttpException('Account already approved');
        }

        if ($this->approvalService->getApprovalStatusOfUser($user) === ApprovalStatus::REJECTED) {
            throw new UnprocessableEntityHttpException('Account already rejected');
        }

        $approval = $this->approvalService->createApproval(
            userId: $user->id,
            companyName: $input->company_name,
            country: $input->country,
            website: $input->website,
            socialLinks: $input->social_links,
            typeOfContent: $input->type_of_content,
            frequency: $input->frequency,
            existingList: $input->existing_list,
            sample: $input->sample,
            whyPost: $input->why_post
        );
        return new JsonResponse(new ApprovalObject($approval));
    }
}
