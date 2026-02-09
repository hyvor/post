<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\OrganizationLevelEndpoint;
use App\Api\Console\Input\Approval\CreateApprovalInput;
use App\Api\Console\Input\Approval\UpdateApprovalInput;
use App\Api\Console\Object\ApprovalObject;
use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use App\Service\Approval\Dto\UpdateApprovalDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApprovalController extends AbstractController
{

    public function __construct(
        private ApprovalService $approvalService,
    )
    {
    }

    private function resolveApproval(string $id): Approval
    {
        $approval = $this->approvalService->getApporvalById((int)$id);

        if ($approval === null) {
            throw new UnprocessableEntityHttpException('Approval not found');
        }

        return $approval;
    }

    #[Route('/approvals', methods: 'GET')]
    #[OrganizationLevelEndpoint]
    public function getApproval(Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);
        $approval = $this->approvalService->getApprovalOfUser($user);

        return new JsonResponse([
            'approval' => ($approval !== null) ? new ApprovalObject($approval) : null
        ]);
    }

    #[Route('/approvals', methods: 'POST')]
    #[OrganizationLevelEndpoint]
    public function approve(
        Request                                  $request,
        #[MapRequestPayload] CreateApprovalInput $input
    ): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);
        $organization = AuthorizationListener::getOrganization($request);

        if ($this->approvalService->getApprovalStatusOfUser($user) === ApprovalStatus::APPROVED) {
            throw new UnprocessableEntityHttpException('Account already approved');
        }

        if ($this->approvalService->getApprovalStatusOfUser($user) === ApprovalStatus::REJECTED) {
            throw new UnprocessableEntityHttpException('Account already rejected');
        }

        $approval = $this->approvalService->createApproval(
            userId: $user->id,
            organizationId: $organization->id,
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

    #[Route('/approvals/{id}', methods: 'PATCH')]
    #[OrganizationLevelEndpoint]
    public function updateApproval(
        Request                                  $request,
        string                                   $id,
        #[MapRequestPayload] UpdateApprovalInput $input
    ): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);
        $approval = $this->resolveApproval($id);

        $userApprovalStatus = $this->approvalService->getApprovalStatusOfUser($user);
        if (($userApprovalStatus !== ApprovalStatus::REVIEWING) && ($userApprovalStatus !== ApprovalStatus::PENDING)) {
            throw new UnprocessableEntityHttpException('Approval is not in pending or reviewing status');
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

        $updates->status = ApprovalStatus::REVIEWING;
        $approval = $this->approvalService->updateApproval($approval, $updates);

        return new JsonResponse(new ApprovalObject($approval));
    }
}
