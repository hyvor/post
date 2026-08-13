<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\SendingProfile\CreateSendingProfileInput;
use App\Api\Console\Input\SendingProfile\UpdateSendingProfileInput;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\Domain;
use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Service\Domain\DomainService;
use App\Service\SendingProfile\Dto\UpdateSendingProfileDto;
use App\Service\SendingProfile\SendingProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class SendingProfileController extends AbstractController
{
    public function __construct(
        private SendingProfileService $sendingProfileService,
        private DomainService $domainService,
    ) {}

    private function getDomainFromEmail(string $email): Domain
    {
        $domainName = explode("@", $email)[1];
        $domain = $this->domainService->getDomainByDomainName($domainName);
        if (!$domain) {
            throw new BadRequestHttpException("Domain not found");
        }
        if (!$domain->isVerifiedInRelay()) {
            throw new BadRequestHttpException("Domain is not verified");
        }
        return $domain;
    }

    #[Route('/sending-profiles', methods: 'GET')]
    #[ScopeRequired(PostScope::SENDING_PROFILES_READ)]
    #[OA\Get(
        description: 'Get all sending profiles of the newsletter.',
        summary: 'Get sending profiles',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of sending profiles',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SendingProfileObject::class))
        )
    )]
    public function getSendingProfiles(Newsletter $newsletter): JsonResponse
    {
        $sendingProfiles = array_map(
            fn(SendingProfile $sendingProfile) => new SendingProfileObject($sendingProfile),
            $this->sendingProfileService->getSendingProfiles($newsletter),
        );
        return $this->json($sendingProfiles);
    }

    #[Route('/sending-profiles', methods: 'POST')]
    #[ScopeRequired(PostScope::SENDING_PROFILES_WRITE)]
    #[OA\Post(
        description: 'Creates a new sending profile for the newsletter. The from email\'s domain must be a verified domain.',
        summary: 'Create a sending profile',
    )]
    #[OA\Response(
        response: 200,
        description: 'Sending profile created successfully',
        content: new Model(type: SendingProfileObject::class)
    )]
    public function createSendingProfile(
        #[MapRequestPayload] CreateSendingProfileInput $input,
        Newsletter $newsletter,
    ): JsonResponse {
        $domain = $this->getDomainFromEmail($input->from_email);
        $sendingProfile = $this->sendingProfileService->createSendingProfile(
            $newsletter,
            $domain,
            $input->from_email,
            $input->from_name,
            $input->reply_to_email,
            $input->brand_name,
            $input->brand_logo,
            $input->brand_url,
        );

        return $this->json(new SendingProfileObject($sendingProfile));
    }

    #[Route('/sending-profiles/{id}', methods: 'PATCH')]
    #[ScopeRequired(PostScope::SENDING_PROFILES_WRITE)]
    #[OA\Patch(
        description: 'Updates a sending profile of the newsletter.',
        summary: 'Update a sending profile',
    )]
    #[OA\Response(
        response: 200,
        description: 'Sending profile updated successfully',
        content: new Model(type: SendingProfileObject::class)
    )]
    public function updateSendingProfile(
        SendingProfile $sendingProfile,
        #[MapRequestPayload] UpdateSendingProfileInput $input,
    ): JsonResponse {
        $updates = new UpdateSendingProfileDto();
        if ($input->has('from_email')) {
            $domain = $this->getDomainFromEmail($input->from_email);
            $updates->customDomain = $domain;
            $updates->fromEmail = $input->from_email;
        }

        if ($input->has('from_name')) {
            $updates->fromName = $input->from_name;
        }

        if ($input->has('reply_to_email')) {
            $updates->replyToEmail = $input->reply_to_email;
        }

        if ($input->has('brand_name')) {
            $updates->brandName = $input->brand_name;
        }

        if ($input->has('brand_logo')) {
            $updates->brandLogo = $input->brand_logo;
        }

        if ($input->has('brand_url')) {
            $updates->brandUrl = $input->brand_url;
        }

        if ($input->has('is_default')) {
            $updates->isDefault = $input->is_default;
        }

        $sendingProfile = $this->sendingProfileService->updateSendingProfile($sendingProfile, $updates);

        return $this->json(new SendingProfileObject($sendingProfile));
    }

    #[Route('/sending-profiles/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::SENDING_PROFILES_WRITE)]
    #[OA\Delete(
        description: 'Deletes a non-system sending profile of the newsletter. The system sending profile cannot be deleted.',
        summary: 'Delete a sending profile',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the remaining sending profiles of the newsletter.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SendingProfileObject::class))
        )
    )]
    public function deleteSendingProfile(SendingProfile $sendingProfile): JsonResponse
    {
        if ($sendingProfile->getIsSystem()) {
            throw new BadRequestHttpException("Cannot delete system sending profile");
        }

        $this->sendingProfileService->deleteSendingProfile($sendingProfile);
        $sendingProfiles = $this->sendingProfileService->getSendingProfiles($sendingProfile->getNewsletter());

        return $this->json(
            array_map(
                fn(SendingProfile $profile) => new SendingProfileObject($profile),
                $sendingProfiles,
            ),
        );
    }
}
