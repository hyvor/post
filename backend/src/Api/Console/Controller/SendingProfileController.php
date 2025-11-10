<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
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

class SendingProfileController extends AbstractController
{
    public function __construct(
        private SendingProfileService $sendingProfileService,
        private DomainService         $domainService
    )
    {
    }

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
    #[ScopeRequired(Scope::SENDING_PROFILES_READ)]
    public function getSendingProfiles(Newsletter $newsletter): JsonResponse
    {
        $sendingProfiles = array_map(
            fn(SendingProfile $sendingProfile) => new SendingProfileObject($sendingProfile),
            $this->sendingProfileService->getSendingProfiles($newsletter)
        );
        return $this->json($sendingProfiles);
    }

    #[Route('/sending-profiles', methods: 'POST')]
    #[ScopeRequired(Scope::SENDING_PROFILES_WRITE)]
    public function createSendingProfile(
        #[MapRequestPayload] CreateSendingProfileInput $input,
        Newsletter                                     $newsletter,
    ): JsonResponse
    {
        $domain = $this->getDomainFromEmail($input->from_email);
        $sendingProfile = $this->sendingProfileService->createSendingProfile(
            $newsletter,
            $domain,
            $input->from_email,
            $input->from_name,
            $input->reply_to_email,
            $input->brand_name,
            $input->brand_logo
        );

        return $this->json(new SendingProfileObject($sendingProfile));
    }

    #[Route('/sending-profiles/{id}', methods: 'PATCH')]
    #[ScopeRequired(Scope::SENDING_PROFILES_WRITE)]
    public function updateSendingProfile(
        SendingProfile                                 $sendingProfile,
        #[MapRequestPayload] UpdateSendingProfileInput $input
    ): JsonResponse
    {

        $updates = new UpdateSendingProfileDto();
        if ($input->hasProperty('from_email')) {
            $domain = $this->getDomainFromEmail($input->from_email);
            $updates->customDomain = $domain;
            $updates->fromEmail = $input->from_email;
        }

        if ($input->hasProperty('from_name')) {
            $updates->fromName = $input->from_name;
        }

        if ($input->hasProperty('reply_to_email')) {
            $updates->replyToEmail = $input->reply_to_email;
        }

        if ($input->hasProperty('brand_name')) {
            $updates->brandName = $input->brand_name;
        }

        if ($input->hasProperty('brand_logo')) {
            $updates->brandLogo = $input->brand_logo;
        }

        if ($input->hasProperty('is_default')) {
            $updates->isDefault = $input->is_default;
        }

        $sendingProfile = $this->sendingProfileService->updateSendingProfile($sendingProfile, $updates);

        return $this->json(new SendingProfileObject($sendingProfile));
    }

    #[Route('/sending-profiles/{id}', methods: 'DELETE')]
    #[ScopeRequired(Scope::SENDING_PROFILES_WRITE)]
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
                $sendingProfiles
            )
        );
    }
}
