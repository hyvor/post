<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Approval\CreateApprovalInput;
use App\Api\Console\Input\Approval\UpdateApprovalInput;
use App\Api\Console\Object\ApprovalObject;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use App\Service\Approval\Dto\UpdateApprovalDto;
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

    #[Route('/approvals/{id}', methods: 'POST')]
    public function updateApproval(
        string $id,
        #[MapRequestPayload] UpdateApprovalInput $input
    ): JsonResponse
    {
        $id = intval($id);
        $user = $this->getHyvorUser();

        $approval = $this->approvalService->getApporvalById($id);

        if (!$approval) {
            throw new UnprocessableEntityHttpException('Approval request not found');
        }

        if ($this->approvalService->getApprovalStatusOfUser($user) !== ApprovalStatus::REVIEWING) {
            throw new UnprocessableEntityHttpException('Approval is not in reviewing status');
        }

        $updates = new UpdateApprovalDto();

        if ($input->hasProperty('company_name')) {
            $updates->companyName = $input->company_name;
        }

        if ($input->hasProperty('country')) {
            $updates->country = $input->country;
        }

        if ($input->hasProperty('website')) {
            $updates->website = $input->website;
        }

        if ($input->hasProperty('social_links')) {
            $updates->socialLinks = $input->social_links;
        }

        if ($input->hasProperty('type_of_content')) {
            $updates->typeOfContent = $input->type_of_content;
        }

        if ($input->hasProperty('frequency')) {
            $updates->frequency = $input->frequency;
        }

        if ($input->hasProperty('existing_list')) {
            $updates->existingList = $input->existing_list;
        }

        if ($input->hasProperty('sample')) {
            $updates->sample = $input->sample;
        }

        if ($input->hasProperty('why_post')) {
            $updates->whyPost = $input->why_post;
        }

        $approval = $this->approvalService->updateApproval($approval, $updates);

        return new JsonResponse(new ApprovalObject($approval));
    }
}
