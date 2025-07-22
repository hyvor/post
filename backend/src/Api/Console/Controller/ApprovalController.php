<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Approval\CreateApprovalInput;
use App\Api\Console\Object\ApprovalObject;
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

    #[Route('/approvals', methods: 'POST')]
    public function approve(
        #[MapRequestPayload] CreateApprovalInput $input
    ): JsonResponse
    {
        $user = $this->getHyvorUser();

        if ($this->approvalService->isUserApproved($user)) {
            throw new UnprocessableEntityHttpException('User already approved');
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
