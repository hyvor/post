<?php

namespace App\Service\SendingEmail;

use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Domain;
use App\Repository\SendingProfileRepository;
use App\Service\AppConfig;
use App\Service\SendingEmail\Dto\UpdateSendingProfileDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SendingProfileService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SendingProfileRepository $sendingEmailRepository,
        private AppConfig $appConfig,
    ) {
    }

    /**
     * @return array<int, SendingProfile>
     */
    public function getSendingProfiles(Newsletter $newsletter): array
    {
        return $this->sendingEmailRepository->findBy(['newsletter' => $newsletter], ['id' => 'ASC']);
    }

    public function getSendingProfileesCount(Newsletter $newsletter): int
    {
        return $this->sendingEmailRepository->count(['newsletter' => $newsletter]);
    }

    public function createSendingProfile(
        Newsletter $newsletter,
        Domain $customDomain,
        string $fromEmail,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        ?string $brandName = null,
        ?string $brandLogo = null
    ): SendingProfile
    {
        $sendingProfile = new SendingProfile();
        $sendingProfile->setNewsletter($newsletter);
        $sendingProfile->setDomain($customDomain);
        $sendingProfile->setFromEmail($fromEmail);
        $sendingProfile->setFromName($fromName) ?? $newsletter->getName();
        $sendingProfile->setReplyToEmail($replyToEmail) ?? $fromEmail;
        $sendingProfile->setBrandName($brandName) ?? $newsletter->getName();
        if ($brandLogo) {
            $sendingProfile->setBrandLogo($brandLogo);
        }
        $sendingProfile->setIsDefault($this->getSendingProfileesCount($newsletter) === 0);
        $sendingProfile->setCreatedAt(new \DateTimeImmutable());
        $sendingProfile->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingProfile);
        $this->em->flush();
        return $sendingProfile;
    }

    public function updateSendingProfile(
        SendingProfile $sendingProfile,
        UpdateSendingProfileDto $updates
    ): SendingProfile {
        if ($updates->hasProperty('fromEmail')) {
            $sendingProfile->setFromEmail($updates->fromEmail);
        }

        if ($updates->hasProperty('fromName')) {
            $sendingProfile->setFromName($updates->fromName);
        }

        if ($updates->hasProperty('replyToEmail')) {
            $sendingProfile->setReplyToEmail($updates->replyToEmail);
        }

        if ($updates->hasProperty('brandName')) {
            $sendingProfile->setBrandName($updates->brandName);
        }

        if ($updates->hasProperty('brandLogo')) {
            $sendingProfile->setBrandLogo($updates->brandLogo);
        }

        if ($updates->hasProperty('customDomain')) {
            $sendingProfile->setDomain($updates->customDomain);
        }

        if ($updates->hasProperty('isDefault')) {
            // only true is supported
            assert($updates->isDefault === true);
            $sendingProfile->setIsDefault($updates->isDefault);

            $currentDefaultSendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter(
                $sendingProfile->getNewsletter()
            );

            if ($currentDefaultSendingProfile) {
                $currentDefaultSendingProfile->setIsDefault(false);
                $currentDefaultSendingProfile->setUpdatedAt($this->now());
            }
        }

        $sendingProfile->setUpdatedAt($this->now());

        $this->em->flush();
        return $sendingProfile;
    }

    public function getCurrentDefaultSendingProfileOfNewsletter(Newsletter $newsletter): ?SendingProfile
    {
        return $this->sendingEmailRepository->findOneBy([
            'newsletter' => $newsletter,
            'isDefault' => true
        ]);
    }

    public function getDefaultEmailAddressOfNewsletterWithFallback(Newsletter $newsletter): string
    {
        $sendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter($newsletter);

        if ($sendingProfile) {
            return $sendingProfile->getEmail();
        }

        return $this->getFallbackAddressOfNewsletter($newsletter);
    }

    public function getFallbackAddressOfNewsletter(Newsletter $newsletter): string
    {
        return sprintf(
            "%s@%s",
            $newsletter->getSlug(),
            $this->appConfig->getDefaultEmailDomain()
        );
    }

    public function deleteSendingProfile(SendingProfile $sendingProfile): void
    {
        $this->em->remove($sendingProfile);

        if ($sendingProfile->getIsDefault()) {
            $profiles = $this->getSendingProfiles($sendingProfile->getNewsletter());

            foreach ($profiles as $profile) {
                if ($profile->getId() !== $sendingProfile->getId()) {
                    $profile->setIsDefault(true);
                    $profile->setUpdatedAt($this->now());
                    $this->em->persist($profile);
                    break;
                }
            }
        }

        $this->em->flush();
    }
}
